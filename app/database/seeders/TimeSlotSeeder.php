<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём слоты на 7 дней вперёд
        for ($day = 0; $day < 7; $day++) {
            $date = now()->addDays($day)->setTime(10, 0);

            for ($hour = 10; $hour <= 18; $hour++) {
                $start = (clone $date)->setTime($hour, 0);
                $end = (clone $date)->setTime($hour + 1, 0);

                TimeSlot::updateOrCreate(
                    ['start_time' => $start, 'end_time' => $end],
                    ['start_time' => $start, 'end_time' => $end]
                );
            }
        }
    }
}
