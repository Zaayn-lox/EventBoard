@extends('layouts.boardy')

@section('title', 'Создать событие — EventBoard')

@section('content')
    <h1 class="page-title">Создать событие</h1>
    <p class="page-subtitle">Добавьте простое студенческое мероприятие в общую ленту.</p>

    <div class="form-card">
        <form method="POST" action="{{ route('events.store') }}" class="form-grid">
            @csrf

            <div>
                <label for="title">Название</label>
                <input id="title" name="title" value="{{ old('title') }}" required>
            </div>

            <div>
                <label for="category_id">Категория</label>
                <select id="category_id" name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="location">Место</label>
                <input id="location" name="location" value="{{ old('location') }}" required>
            </div>

            <div>
                <label for="starts_at">Дата и время начала</label>
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required>
            </div>

            <div>
                <label for="ends_at">Дата и время окончания</label>
                <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}">
            </div>

            <div>
                <label for="image_url">Картинка</label>
                <select id="image_url" name="image_url">
                    <option value="/images/events/hackathon.svg">Хакатон</option>
                    <option value="/images/events/career.svg">Карьера</option>
                    <option value="/images/events/games.svg">Настольные игры</option>
                </select>
            </div>

            <div>
                <label for="description">Описание</label>
                <textarea id="description" name="description" required>{{ old('description') }}</textarea>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Создать</button>
                <a class="btn light" href="{{ route('events.index') }}">Отмена</a>
            </div>
        </form>
    </div>
@endsection
