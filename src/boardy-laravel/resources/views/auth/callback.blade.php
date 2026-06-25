@extends('layouts.boardy')

@section('title', 'Авторизация')

@section('content')
    <div class="card">
        <h1>Авторизация выполнена</h1>

        <p>
            Вход через внешний сервис успешно завершён.
        </p>

        <div class="actions">
            <a class="btn primary" href="{{ route('events.index') }}">
                Перейти к событиям
            </a>
        </div>
    </div>
@endsection
