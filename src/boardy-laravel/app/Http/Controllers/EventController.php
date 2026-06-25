<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->get('category');

        $events = Event::with(['category'])
            ->withCount(['registrations as participants_count'])
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->orderBy('starts_at')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('events.index', [
            'events' => $events,
            'categories' => $categories,
            'activeCategory' => $categorySlug,
        ]);
    }

    public function show(Event $event)
    {
        $event->load(['category', 'author']);
        $event->loadCount(['registrations as participants_count']);

        $isJoined = false;

        if (Auth::check()) {
            $isJoined = $event->registrations()
                ->where('user_id', Auth::id())
                ->exists();
        }

        return view('events.show', [
            'event' => $event,
            'isJoined' => $isJoined,
            'participantsCount' => $event->participants_count,
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('events.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
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

    public function edit(Event $event)
    {
        $categories = Category::orderBy('name')->get();

        return view('events.edit', [
            'event' => $event,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        if (empty($data['image_url'])) {
            $data['image_url'] = '/images/events/hackathon.svg';
        }

        $event->update($data);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Событие обновлено.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('status', 'Событие удалено.');
    }
}
