<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'EventBoard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .topbar {
            background: #111827;
            color: white;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-weight: 800;
            font-size: 22px;
            letter-spacing: 0.3px;
        }

        .nav {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav a,
        .nav button {
            color: white;
            background: transparent;
            border: 1px solid rgba(255,255,255,.25);
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .nav a:hover,
        .nav button:hover {
            background: rgba(255,255,255,.12);
        }

        .container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px 18px 50px;
        }

        .status {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .errors {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .page-title {
            font-size: 34px;
            margin: 0 0 10px;
        }

        .page-subtitle {
            color: #6b7280;
            margin: 0 0 22px;
            line-height: 1.5;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .filter {
            background: white;
            border: 1px solid #d1d5db;
            padding: 9px 13px;
            border-radius: 999px;
            color: #374151;
        }

        .filter.active {
            background: #111827;
            color: white;
            border-color: #111827;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15,23,42,.08);
            border: 1px solid #e5e7eb;
        }

        .card-image {
            width: 100%;
            height: 170px;
            object-fit: cover;
            display: block;
            background: #e5e7eb;
        }

        .card-body {
            padding: 18px;
        }

        .badge {
            display: inline-block;
            background: #e0f2fe;
            color: #075985;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .card-title {
            margin: 0 0 10px;
            font-size: 21px;
        }

        .meta {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .description {
            color: #374151;
            line-height: 1.6;
        }

        .actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            border: 0;
            background: #2563eb;
            color: white;
            padding: 10px 14px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn.secondary {
            background: #374151;
        }

        .btn.danger {
            background: #dc2626;
        }

        .btn.light {
            background: #e5e7eb;
            color: #111827;
        }

        .form-card {
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px rgba(15,23,42,.08);
            border-radius: 18px;
            padding: 22px;
        }

        .form-grid {
            display: grid;
            gap: 16px;
        }

        label {
            font-weight: 700;
            display: block;
            margin-bottom: 7px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 15px;
            background: white;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .event-hero {
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15,23,42,.08);
            border: 1px solid #e5e7eb;
        }

        .event-hero img {
            width: 100%;
            max-height: 420px;
            object-fit: cover;
            display: block;
        }

        .event-content {
            padding: 24px;
        }

        .participants-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px 16px;
            margin: 18px 0;
            font-weight: 700;
        }

        .empty {
            background: white;
            padding: 24px;
            border-radius: 16px;
            color: #6b7280;
            border: 1px dashed #d1d5db;
        }
    </style>

    @stack('head')
</head>
<body>
    <header class="topbar">
        <a href="{{ route('events.index') }}" class="brand">EventBoard</a>

        <nav class="nav">
            <a href="{{ route('events.index') }}">События</a>

            @auth
                <a href="{{ route('events.create') }}">Создать событие</a>
                <span>{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}">Войти</a>
                <a href="{{ route('register') }}">Регистрация</a>
                <a href="{{ route('auth.github') }}">GitHub OAuth</a>
            @endauth
        </nav>
    </header>

    <main class="container">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>Проверьте форму:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
