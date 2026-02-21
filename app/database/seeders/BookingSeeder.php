<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Car;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::all();
        $slots = TimeSlot::all();

        // Создаём несколько демо-броней
        $demoBookings = [
            ['car_index' => 0, 'slot_index' => 0, 'name' => 'Ivan Petrov', 'phone' => '+79990000001', 'status' => 'confirmed'],
            ['car_index' => 1, 'slot_index' => 5, 'name' => 'Maria Sidorova', 'phone' => '+79990000002', 'status' => 'pending'],
            ['car_index' => 2, 'slot_index' => 10, 'name' => 'Alex Smirnov', 'phone' => '+79990000003', 'status' => 'cancelled'],
        ];

        foreach ($demoBookings as $booking) {
            if ($cars->has($booking['car_index']) && $slots->has($booking['slot_index'])) {
                Booking::updateOrCreate(
                    [
                        'car_id' => $cars->get($booking['car_index'])->id,
                        'slot_id' => $slots->get($booking['slot_index'])->id,
                    ],
                    [
                        'customer_name' => $booking['name'],
                        'customer_phone' => $booking['phone'],
                        'status' => $booking['status'],
                    ]
                );
            }
        }
    }
}
