<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class EventRegistrationController extends Controller
{
    public function store(Event $event): RedirectResponse
    {
        EventRegistration::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
        ]);

        $this->publishParticipantsUpdate($event);

        return back()->with('status', 'Вы зарегистрировались на событие.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        EventRegistration::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->delete();

        $this->publishParticipantsUpdate($event);

        return back()->with('status', 'Вы отменили участие.');
    }

    private function publishParticipantsUpdate(Event $event): void
    {
        $participantsCount = $event->registrations()->count();

        $payload = [
            'type' => 'event_participants_updated',
            'event_id' => $event->id,
            'participants_count' => $participantsCount,
        ];

        try {
            $message = json_encode($payload, JSON_UNESCAPED_UNICODE);

            $subscribers = $this->publishToRedis(
                'event_updates',
                $message
            );

            Log::info('EventBoard direct Redis publish success', [
                'payload' => $payload,
                'subscribers' => $subscribers,
            ]);
        } catch (Throwable $exception) {
            Log::error('EventBoard direct Redis publish failed', [
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            report($exception);
        }
    }

    private function publishToRedis(string $channel, string $message): int
    {
        $host = env('REDIS_HOST', 'redis');
        $port = (int) env('REDIS_PORT', 6379);

        $socket = stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            2
        );

        if (!$socket) {
            throw new \RuntimeException("Redis connection failed: {$errstr}");
        }

        $command =
            "*3\r\n" .
            "$7\r\nPUBLISH\r\n" .
            "$" . strlen($channel) . "\r\n{$channel}\r\n" .
            "$" . strlen($message) . "\r\n{$message}\r\n";

        fwrite($socket, $command);

        $response = fgets($socket);
        fclose($socket);

        if (!$response || !str_starts_with($response, ':')) {
            return 0;
        }

        return (int) trim(substr($response, 1));
    }
}
