@extends('layouts.boardy')

@section('title', $event->title . ' — EventBoard')

@section('content')
    <div class="event-hero">
        <img
            src="{{ $event->image_url ?: '/images/events/hackathon.svg' }}"
            alt="{{ $event->title }}"
        >

        <div class="event-content">
            <span class="badge">{{ $event->category->name }}</span>

            <h1 class="page-title">{{ $event->title }}</h1>

            <div class="meta">
                <div>Дата и время: {{ $event->starts_at->format('d.m.Y H:i') }}</div>

                @if ($event->ends_at)
                    <div>Окончание: {{ $event->ends_at->format('d.m.Y H:i') }}</div>
                @endif

                <div>Место: {{ $event->location }}</div>
                <div>Организатор: {{ $event->author->name }}</div>
            </div>

            <div
                id="event-participants-root"
                class="participants-box"
                data-event-id="{{ $event->id }}"
                data-count="{{ $participantsCount }}"
            >
                Участников:
                <span id="participants-count-{{ $event->id }}">{{ $participantsCount }}</span>
            </div>

            <p class="description">{{ $event->description }}</p>

            <div class="actions">
                @auth
                    @if ($isJoined)
                        <form method="POST" action="{{ route('events.leave', $event) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn secondary" type="submit">Отменить участие</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('events.join', $event) }}">
                            @csrf
                            <button class="btn" type="submit">Я пойду</button>
                        </form>
                    @endif

                    @if ($event->user_id === auth()->id())
                        <a class="btn light" href="{{ route('events.edit', $event) }}">Редактировать</a>

                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Удалить событие?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger" type="submit">Удалить</button>
                        </form>
                    @endif
                @else
                    <a class="btn" href="{{ route('login') }}">Войти, чтобы участвовать</a>
                    <a class="btn secondary" href="{{ route('auth.github') }}">Войти через GitHub</a>
                @endauth

                <a class="btn light" href="{{ route('events.index') }}">Назад к событиям</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/js/event-participants.js"></script>
@endpush
