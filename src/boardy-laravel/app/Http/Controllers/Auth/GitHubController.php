<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GitHubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->stateless()
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')
                ->stateless()
                ->user();

            $githubId = (string) $githubUser->getId();

            $email = $githubUser->getEmail();

            if (!$email) {
                $email = 'github_' . $githubId . '@eventboard.local';
            }

            $name = $githubUser->getName()
                ?: $githubUser->getNickname()
                ?: 'GitHub User';

            $user = User::where('github_id', $githubId)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Str::password(32),
                    'github_id' => $githubId,
                ]);
            } else {
                $user->update([
                    'name' => $user->name ?: $name,
                    'email' => $user->email ?: $email,
                    'github_id' => $githubId,
                ]);
            }

            Auth::login($user, true);

            return redirect()
                ->route('events.index')
                ->with('status', 'Вы вошли через GitHub.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'github' => 'Не удалось войти через GitHub. Проверьте OAuth-настройки.',
                ]);
        }
    }
}
