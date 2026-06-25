@extends('layouts.boardy')

@section('title', 'EventBoard — студенческие события')

@section('content')
    <h1 class="page-title">Студенческие события</h1>
    <p class="page-subtitle">
        Простая лента мероприятий для студентов: учебные встречи, IT-события, карьера и досуг.
    </p>

    <div class="filters">
        <a class="filter {{ $activeCategory === '' ? 'active' : '' }}" href="{{ route('events.index') }}">
            Все
        </a>

        @foreach ($categories as $category)
            <a
                class="filter {{ $activeCategory === $category->slug ? 'active' : '' }}"
                href="{{ route('events.index', ['category' => $category->slug]) }}"
            >
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    @if ($events->isEmpty())
        <div class="empty">Событий пока нет.</div>
    @else
        <div class="grid">
            @foreach ($events as $event)
                <article class="card">
                    <img
                        class="card-image"
                        src="{{ $event->image_url ?: '/images/events/hackathon.svg' }}"
                        alt="{{ $event->title }}"
                    >

                    <div class="card-body">
                        <span class="badge">{{ $event->category->name }}</span>

                        <h2 class="card-title">
                            <a href="{{ route('events.show', $event) }}">
                                {{ $event->title }}
                            </a>
                        </h2>

                        <div class="meta">
                            <div>Дата: {{ $event->starts_at->format('d.m.Y H:i') }}</div>
                            <div>Место: {{ $event->location }}</div>
                            <div>Участников: {{ $event->participants_count ?? 0 }}</div>
                        </div>

                        <p class="description">
                            {{ \Illuminate\Support\Str::limit($event->description, 120) }}
                        </p>

                        <div class="actions">
                            <a class="btn" href="{{ route('events.show', $event) }}">Подробнее</a>

                            @auth
                                @if ($event->user_id === auth()->id())
                                    <a class="btn light" href="{{ route('events.edit', $event) }}">Редактировать</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
