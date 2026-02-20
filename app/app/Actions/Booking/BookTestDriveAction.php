<?php

namespace App\Actions\Booking;

use App\DTO\BookingData;
use App\Models\Booking;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\DB;
use App\Exceptions\SlotAlreadyBookedException;

class BookTestDriveAction
{
    public function execute(BookingData $data): Booking
    {
        // Транзакция гарантирует атомарность: либо всё успешно, либо откат
        return DB::transaction(function () use ($data) {

            // 1. Блокируем строку слота (SELECT FOR UPDATE)
            // Это предотвратит гонку данных при одновременных запросах
            $slot = TimeSlot::where('id', $data->slotId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Проверяем, не занята ли уже эта машина в этом слоте
            $exists = Booking::where('car_id', $data->carId)
                ->where('slot_id', $data->slotId)
                ->exists();

            if ($exists) {
                // 409 Conflict — стандартный код для конфликта ресурсов
                throw new SlotAlreadyBookedException('This time slot is already booked for selected car', 409);
            }

            // 3. Создаем бронь
            return Booking::create([
                'car_id' => $data->carId,
                'slot_id' => $data->slotId,
                'customer_name' => $data->customerName,
                'customer_phone' => $data->customerPhone,
                'status' => 'confirmed',
            ]);
        });
    }
}
