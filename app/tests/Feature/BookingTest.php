<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\TimeSlot;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    // Эта черта очищает БД перед каждым тестом
    use RefreshDatabase;

    /**
     * Отправить запрос без логирования
     */
    protected function withoutLogging(): self
    {
        $this->withHeaders(['X-Testing' => 'true']);
        return $this;
    }

    /**
     * Тест 1: Успешное создание брони
     */
    public function test_can_create_booking(): void
    {
        // Arrange (Подготовка данных)
        $car = Car::create(['model' => 'Tesla Model 3', 'vin' => '12345678901234567', 'license_plate' => 'A001AA']);
        $slot = TimeSlot::create(['start_time' => now()->addDay()->setTime(10, 0), 'end_time' => now()->addDay()->setTime(11, 0)]);

        // Act (Действие)
        $response = $this->withoutLogging()->postJson('/api/bookings', [
            'car_id' => $car->id,
            'slot_id' => $slot->id,
            'customer_name' => 'Ivan',
            'customer_phone' => '+79990000000',
        ]);

        // Assert (Проверка результата)
        $response->assertStatus(201) // 201 Created
            ->assertJsonStructure([
                'id',
                'car_id',
                'slot_id',
                'customer_name',
                'status',
            ])
            ->assertJson(['status' => 'confirmed']);

        // Проверка, что запись реально появилась в БД
        $this->assertDatabaseHas('bookings', [
            'car_id' => $car->id,
            'slot_id' => $slot->id,
            'customer_name' => 'Ivan',
        ]);
    }

    /**
     * Тест 2: Защита от овербукинга (двойная бронь)
     */
    public function test_cannot_book_same_slot_twice(): void
    {
        // Arrange
        $car = Car::create(['model' => 'BMW X5', 'vin' => '77777777777777777', 'license_plate' => 'B002BB']);
        $slot = TimeSlot::create(['start_time' => now()->addDay()->setTime(14, 0), 'end_time' => now()->addDay()->setTime(15, 0)]);

        // Первая бронь — успешная
        $this->withoutLogging()->postJson('/api/bookings', [
            'car_id' => $car->id,
            'slot_id' => $slot->id,
            'customer_name' => 'Ivan',
            'customer_phone' => '+79990000000',
        ])->assertStatus(201);

        // Act: Вторая попытка забронировать то же самое
        $response = $this->withoutLogging()->postJson('/api/bookings', [
            'car_id' => $car->id,
            'slot_id' => $slot->id,
            'customer_name' => 'Petr',
            'customer_phone' => '+79991111111',
        ]);

        // Assert: Должен быть 409 Conflict
        $response->assertStatus(409)
            ->assertJson(['error' => 'Conflict']);
    }

    /**
     * Тест 3: Валидация данных (отсутствие обязательных полей)
     */
    public function test_validation_fails_without_required_fields(): void
    {
        // Act: Отправляем пустой запрос
        $response = $this->withoutLogging()->postJson('/api/bookings');

        // Assert: Должна быть ошибка 422
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['car_id', 'slot_id', 'customer_name', 'customer_phone']);
    }
}
