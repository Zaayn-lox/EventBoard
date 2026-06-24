@extends('layouts.boardy')

@section('title', 'Редактировать событие — EventBoard')

@section('content')
    <h1 class="page-title">Редактировать событие</h1>
    <p class="page-subtitle">{{ $event->title }}</p>

    <div class="form-card">
        <form method="POST" action="{{ route('events.update', $event) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div>
                <label for="title">Название</label>
                <input id="title" name="title" value="{{ old('title', $event->title) }}" required>
            </div>

            <div>
                <label for="category_id">Категория</label>
                <select id="category_id" name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $event->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="location">Место</label>
                <input id="location" name="location" value="{{ old('location', $event->location) }}" required>
            </div>

            <div>
                <label for="starts_at">Дата и время начала</label>
                <input
                    id="starts_at"
                    name="starts_at"
                    type="datetime-local"
                    value="{{ old('starts_at', optional($event->starts_at)->format('Y-m-d\TH:i')) }}"
                    required
                >
            </div>

            <div>
                <label for="ends_at">Дата и время окончания</label>
                <input
                    id="ends_at"
                    name="ends_at"
                    type="datetime-local"
                    value="{{ old('ends_at', optional($event->ends_at)->format('Y-m-d\TH:i')) }}"
                >
            </div>

            <div>
                <label for="image_url">Картинка</label>
                <select id="image_url" name="image_url">
                    <option value="/images/events/hackathon.svg" @selected(old('image_url', $event->image_url) === '/images/events/hackathon.svg')>Хакатон</option>
                    <option value="/images/events/career.svg" @selected(old('image_url', $event->image_url) === '/images/events/career.svg')>Карьера</option>
                    <option value="/images/events/games.svg" @selected(old('image_url', $event->image_url) === '/images/events/games.svg')>Настольные игры</option>
                </select>
            </div>

            <div>
                <label for="description">Описание</label>
                <textarea id="description" name="description" required>{{ old('description', $event->description) }}</textarea>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Сохранить</button>
                <a class="btn light" href="{{ route('events.show', $event) }}">Отмена</a>
            </div>
        </form>
    </div>
@endsection
