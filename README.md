EventBoard
EventBoard — это небольшой агрегатор студенческих событий.
Проект сделан в рамках учебной работы по веб-архитектуре.
Основная идея сервиса — дать студентам простой сайт, где можно посмотреть список мероприятий, открыть страницу конкретного события, зарегистрироваться на участие и увидеть обновление количества участников в реальном времени.
Проект построен на связке:
•	Laravel;
•	FastAPI;
•	MySQL;
•	Redis;
•	WebSocket;
•	Nginx;
•	Docker Compose;
•	GitHub OAuth.
Основные адреса
Основной сайт:
http://eventboard.belyaevubuntu.ru/events
API:
http://api.eventboard.belyaevubuntu.ru/api/events
Health check API:
http://api.eventboard.belyaevubuntu.ru/api/health
GitHub OAuth callback:
http://eventboard.belyaevubuntu.ru/auth/github/callback
Что умеет проект
В текущей версии реализованы основные функции:
•	просмотр списка студенческих мероприятий;
•	фильтрация событий по категориям;
•	просмотр отдельной страницы события;
•	создание нового события;
•	редактирование события;
•	удаление события;
•	регистрация пользователя на событие;
•	отмена участия;
•	отображение количества участников;
•	обновление количества участников через WebSocket без перезагрузки страницы;
•	вход через GitHub OAuth;
•	REST API для получения событий;
•	отдельный API-домен для FastAPI;
•	запуск всех сервисов через Docker Compose.
Пример пользовательского сценария
Пользователь открывает сайт:
http://eventboard.belyaevubuntu.ru/events
На странице отображается список мероприятий.
У каждого события есть:
•	название;
•	категория;
•	дата и время;
•	место проведения;
•	картинка;
•	краткое описание;
•	количество участников;
•	кнопка перехода на подробную страницу.
На странице события пользователь может нажать кнопку:
Я пойду
После этого Laravel записывает участие в базу данных, а счётчик участников обновляется.
Если эта же страница открыта во второй вкладке, то количество участников меняется там автоматически через WebSocket.
Архитектура проекта
Общая схема работы:
Браузер
   |
   v
Nginx
   |
   |--- eventboard.belyaevubuntu.ru ------> Laravel
   |
   |--- api.eventboard.belyaevubuntu.ru --> FastAPI
                                           |
                                           |--- MySQL
                                           |--- Redis
                                           |--- WebSocket
Laravel отвечает за страницы сайта, авторизацию, GitHub OAuth и запись данных.
FastAPI отвечает за REST API и WebSocket.
Redis используется как канал обмена событиями между Laravel и FastAPI.
MySQL хранит пользователей, мероприятия, категории и регистрации.
Роль Laravel
Laravel в проекте используется как основное SSR-приложение.
Он отвечает за:
•	главную страницу со списком событий;
•	страницу отдельного события;
•	создание события;
•	редактирование события;
•	удаление события;
•	регистрацию пользователя на событие;
•	отмену регистрации;
•	авторизацию;
•	вход через GitHub OAuth;
•	публикацию события в Redis после изменения количества участников.
Основные маршруты Laravel:
GET     /events
GET     /events/{event}
GET     /events/create
POST    /events
GET     /events/{event}/edit
PUT     /events/{event}
DELETE  /events/{event}
POST    /events/{event}/join
DELETE  /events/{event}/join
GET     /auth/github
GET     /auth/github/callback
GET     /health
Роль FastAPI
FastAPI используется как отдельный backend-сервис для API и WebSocket.
Он отвечает за:
•	выдачу списка событий в формате JSON;
•	выдачу одного события;
•	выдачу списка категорий;
•	выдачу количества участников события;
•	WebSocket-подключение;
•	подписку на Redis-канал;
•	отправку realtime-обновлений в браузер.
Основные API endpoints:
GET /api/health
GET /api/categories
GET /api/events
GET /api/events/{event_id}
GET /api/events/{event_id}/participants
WS  /ws
WebSocket-сценарий
Realtime-сценарий работает так:
Пользователь нажимает "Я пойду"
        ↓
Laravel записывает регистрацию в MySQL
        ↓
Laravel считает новое количество участников
        ↓
Laravel публикует сообщение в Redis
        ↓
FastAPI получает сообщение из Redis
        ↓
FastAPI отправляет сообщение всем WebSocket-клиентам
        ↓
Браузер обновляет счётчик участников без перезагрузки
Пример сообщения:
{
  "type": "event_participants_updated",
  "event_id": 1,
  "participants_count": 3
}
Структура базы данных
В проекте используются основные таблицы:
users
categories
events
event_registrations
event_images
users
Таблица пользователей.
Используется для:
•	обычной авторизации;
•	GitHub OAuth;
•	связи пользователя с созданными событиями;
•	связи пользователя с регистрациями на события.
Основные поля:
id
name
email
password
github_id
created_at
updated_at
categories
Таблица категорий событий.
Примеры категорий:
IT
Карьера
Досуг
Основные поля:
id
name
slug
created_at
updated_at
events
Таблица мероприятий.
Основные поля:
id
user_id
category_id
title
description
location
starts_at
ends_at
image_url
created_at
updated_at
event_registrations
Таблица регистраций пользователей на события.
Основные поля:
id
event_id
user_id
created_at
updated_at
Для пары event_id + user_id задана уникальность, чтобы один пользователь не мог записаться на одно и то же событие несколько раз.
event_images
Таблица изображений событий.
Основные поля:
id
event_id
path
alt
sort_order
created_at
updated_at
Связи между таблицами
users 1 --- * events

categories 1 --- * events

events 1 --- * event_registrations

users 1 --- * event_registrations

events 1 --- * event_images
То есть пользователь может создавать события, событие относится к категории, а регистрация связывает пользователя и событие.
Docker-сервисы
Проект запускается через Docker Compose.
Основные контейнеры:
eventboard-nginx
eventboard-laravel
eventboard-api
eventboard-mysql
eventboard-redis
eventboard-nginx
Nginx принимает HTTP-запросы и направляет их:
eventboard.belyaevubuntu.ru      -> Laravel
api.eventboard.belyaevubuntu.ru  -> FastAPI
/ws                              -> FastAPI WebSocket
eventboard-laravel
Laravel-приложение.
Отвечает за сайт, Blade-страницы, авторизацию, события и регистрацию пользователей.
eventboard-api
FastAPI-приложение.
Отвечает за API, WebSocket и получение Redis-событий.
eventboard-mysql
MySQL-база данных.
Хранит пользователей, события, категории и регистрации.
eventboard-redis
Redis.
Используется для Pub/Sub-сценария между Laravel и FastAPI.
Структура проекта
Основные файлы и папки:
.
├── docker
│   ├── mysql
│   │   └── init.sql
│   └── nginx
│       └── default.conf
│
├── src
│   ├── boardy-api
│   │   ├── Dockerfile
│   │   ├── database.py
│   │   ├── main.py
│   │   ├── requirements.txt
│   │   └── routers
│   │       ├── events.py
│   │       └── ws.py
│   │
│   └── boardy-laravel
│       ├── Dockerfile
│       ├── app
│       │   ├── Http
│       │   │   └── Controllers
│       │   └── Models
│       ├── database
│       │   ├── migrations
│       │   └── seeders
│       ├── public
│       │   ├── images
│       │   └── js
│       ├── resources
│       │   └── views
│       └── routes
│           └── web.php
│
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
Переменные окружения
Настоящие .env-файлы не хранятся в репозитории.
В репозитории есть только примеры:
.env.example
src/boardy-laravel/.env.example
Важные параметры Laravel:
APP_NAME=EventBoard
APP_URL=http://eventboard.belyaevubuntu.ru

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=eventboard_laravel
DB_USERNAME=eventboard
DB_PASSWORD=eventboard_password

REDIS_HOST=redis
REDIS_PORT=6379

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://eventboard.belyaevubuntu.ru/auth/github/callback
Файл .env не добавляется в Git, потому что может содержать секреты.
Запуск проекта
Перейти в папку проекта:
cd /home/student/eventboard-work
Запустить контейнеры:
docker compose up -d
Проверить контейнеры:
docker compose ps
Ожидаемые контейнеры:
eventboard-nginx
eventboard-laravel
eventboard-api
eventboard-mysql
eventboard-redis
У пользователя, вошедшего через GitHub, должен быть заполнен github_id.
Основные готовые сценарии
Сценарий 1. Просмотр событий
1.	Открыть сайт.
2.	Перейти на /events.
3.	Посмотреть список событий.
4.	Использовать фильтр категорий.
5.	Открыть отдельное событие.
Сценарий 2. Регистрация на событие
1.	Войти на сайт.
2.	Открыть страницу события.
3.	Нажать Я пойду.
4.	Проверить, что количество участников увеличилось.
Сценарий 3. Realtime-обновление
1.	Открыть одно событие в двух вкладках.
2.	В первой вкладке нажать Я пойду.
3.	Во второй вкладке счётчик участников должен обновиться без перезагрузки.
Сценарий 4. GitHub OAuth
1.	Нажать вход через GitHub.
2.	Подтвердить вход на GitHub.
3.	Вернуться на сайт.
4.	Проверить, что пользователь авторизован.
Сценарий 5. Проверка API
1.	Открыть /api/events.
2.	Получить список событий в JSON.
3.	Открыть /api/events/1.
4.	Получить данные одного события.
5.	Открыть /api/events/1/participants.
6.	Получить количество участников.
Текущий статус проекта
На текущем этапе реализованы:
•	Laravel-страницы;
•	CRUD событий;
•	регистрация на события;
•	FastAPI API;
•	Redis Pub/Sub;
•	WebSocket;
•	GitHub OAuth;
•	Docker Compose;
•	Nginx routing по доменам;
•	MySQL-структура;
•	тестовые события;
•	Git-репозиторий с несколькими коммитами.
