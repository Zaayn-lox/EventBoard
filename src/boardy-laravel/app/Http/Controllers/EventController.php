<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $eventsQuery = Event::query()
            ->with(['category', 'author'])
            ->withCount('registrations')
            ->orderBy('starts_at');

        if ($request->filled('category')) {
            $eventsQuery->whereHas('category', function ($query) use ($request) {
                $query->where('slug', $request->string('category'));
            });
        }

        $events = $eventsQuery->get();

        return view('events.index', [
            'events' => $events,
            'categories' => $categories,
            'activeCategory' => $request->string('category')->toString(),
        ]);
    }

    public function show(Event $event): View
    {
        $event->load(['category', 'author', 'images'])
            ->loadCount('registrations');

        $isJoined = false;

        if (Auth::check()) {
            $isJoined = $event->registrations()
                ->where('user_id', Auth::id())
                ->exists();
        }

        return view('events.show', [
            'event' => $event,
            'isJoined' => $isJoined,
            'participantsCount' => $event->registrations_count,
        ]);
    }

    public function create(): View
    {
        return view('events.create', [
            'categories' => Category::orderBy('name')->get(),
            'event' => new Event(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:3000'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        $data['user_id'] = Auth::id();

        if (empty($data['image_url'])) {
            $data['image_url'] = '/images/events/hackathon.svg';
        }

        $event = Event::create($data);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Событие создано.');
    }

    public function edit(Event $event): View
    {
        $this->ensureOwner($event);

        return view('events.edit', [
            'event' => $event,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->ensureOwner($event);

        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:3000'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        if (empty($data['image_url'])) {
            $data['image_url'] = $event->image_url ?: '/images/events/hackathon.svg';
        }

        $event->update($data);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Событие обновлено.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->ensureOwner($event);

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('status', 'Событие удалено.');
    }

    private function ensureOwner(Event $event): void
    {
        abort_unless(Auth::check() && $event->user_id === Auth::id(), 403);
    }
}
