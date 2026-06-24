<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::create([
            'name' => 'Тестовый пользователь',
            'email' => 'test@eventboard.local',
            'password' => Hash::make('password'),
            'github_id' => null,
        ]);

        $organizer = User::create([
            'name' => 'Организатор EventBoard',
            'email' => 'organizer@eventboard.local',
            'password' => Hash::make('password'),
            'github_id' => null,
        ]);

        $student = User::create([
            'name' => 'Студент',
            'email' => 'student@eventboard.local',
            'password' => Hash::make('password'),
            'github_id' => null,
        ]);

        $it = Category::create([
            'name' => 'IT',
            'slug' => 'it',
        ]);

        $career = Category::create([
            'name' => 'Карьера',
            'slug' => 'career',
        ]);

        $leisure = Category::create([
            'name' => 'Досуг',
            'slug' => 'leisure',
        ]);

        $hackathon = Event::create([
            'user_id' => $organizer->id,
            'category_id' => $it->id,
            'title' => 'Хакатон по веб-разработке',
            'description' => 'Практическое мероприятие для студентов, где участники за вечер создают небольшой веб-проект, знакомятся с командной разработкой и получают обратную связь.',
            'location' => 'Аудитория 302',
            'starts_at' => now()->addDays(5)->setTime(18, 0),
            'ends_at' => now()->addDays(5)->setTime(21, 0),
            'image_url' => '/images/events/hackathon.svg',
        ]);

        $careerMeetup = Event::create([
            'user_id' => $organizer->id,
            'category_id' => $career->id,
            'title' => 'Карьерная встреча с IT-компанией',
            'description' => 'Встреча для студентов, которые хотят узнать о стажировках, junior-вакансиях, требованиях работодателей и карьерном старте в IT.',
            'location' => 'Конференц-зал',
            'starts_at' => now()->addDays(8)->setTime(16, 0),
            'ends_at' => now()->addDays(8)->setTime(18, 0),
            'image_url' => '/images/events/career.svg',
        ]);

        $gamesNight = Event::create([
            'user_id' => $testUser->id,
            'category_id' => $leisure->id,
            'title' => 'Вечер настольных игр',
            'description' => 'Неформальная встреча студентов после занятий. Можно познакомиться с одногруппниками, поиграть в настольные игры и отдохнуть после учебной недели.',
            'location' => 'Студенческий клуб',
            'starts_at' => now()->addDays(10)->setTime(19, 0),
            'ends_at' => now()->addDays(10)->setTime(22, 0),
            'image_url' => '/images/events/games.svg',
        ]);

        foreach ([
            [$hackathon, '/images/events/hackathon.svg', 'Хакатон по веб-разработке'],
            [$careerMeetup, '/images/events/career.svg', 'Карьерная встреча'],
            [$gamesNight, '/images/events/games.svg', 'Вечер настольных игр'],
        ] as [$event, $path, $alt]) {
            EventImage::create([
                'event_id' => $event->id,
                'path' => $path,
                'alt' => $alt,
                'sort_order' => 1,
            ]);
        }

        foreach ([$hackathon, $careerMeetup, $gamesNight] as $event) {
            EventRegistration::firstOrCreate([
                'event_id' => $event->id,
                'user_id' => $testUser->id,
            ]);

            EventRegistration::firstOrCreate([
                'event_id' => $event->id,
                'user_id' => $student->id,
            ]);
        }
    }
}
