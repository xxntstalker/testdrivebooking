<?php

namespace App\Http\Controllers;

use App\Actions\Booking\BookTestDriveAction;
use App\DTO\BookingData;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookTestDriveAction $bookingAction
    ) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        // 1. Преобразуем запрос в DTO
        $dto = BookingData::fromRequest($request->validated());

        // 2. Выполняем бизнес-логику
        $booking = $this->bookingAction->execute($dto);

        // 3. Возвращаем ответ (201 Created)
        return response()->json($booking->load('car', 'slot'), 201);
    }

    public function index(): JsonResponse
    {
        // Список всех броней (для проверки)
        $bookings = Booking::with('car', 'slot')->get();
        return response()->json($bookings);
    }
}
