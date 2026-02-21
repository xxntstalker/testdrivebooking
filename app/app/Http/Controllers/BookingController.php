<?php

namespace App\Http\Controllers;

use App\Actions\Booking\BookTestDriveAction;
use App\DTO\BookingData;
use App\Http\Requests\ListBookingsRequest;
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
        $dto = BookingData::fromRequest($request->validated());

        $booking = $this->bookingAction->execute($dto);

        $booking->load('car', 'slot');

        $bookingData = $booking->toArray();
        $bookingData['slot']['start_time'] = $booking->slot->start_time->format('d.m.Y H:i');
        $bookingData['slot']['end_time'] = $booking->slot->end_time->format('H:i');

        return response()->json($bookingData, 201);
    }

    public function index(ListBookingsRequest $request): JsonResponse
    {
        // Базовый запрос: только активные брони
        $query = Booking::with(['car', 'slot'])
            ->join('time_slots', 'bookings.slot_id', '=', 'time_slots.id') // ← Добавляем JOIN
            ->whereIn('bookings.status', ['pending', 'confirmed'])
            ->orderBy('time_slots.start_time', 'asc')
            ->select('bookings.*');

        // Пагинация: по умолчанию 20 записей на страницу, макс 100
        $perPage = min($request->integer('per_page', 20), 100);

        $bookings = $query->paginate($perPage);

        // Скрываем чувствительные данные
        $bookings->getCollection()->transform(function ($booking) {
            $booking->makeHidden(['customer_name', 'customer_phone', 'created_at', 'updated_at']);

            if ($booking->relationLoaded('car')) {
                $booking->car->makeHidden(['vin', 'license_plate', 'created_at', 'updated_at']);
            }
            if ($booking->relationLoaded('slot')) {
                $booking->slot->makeHidden(['created_at', 'updated_at']);
            }
            return $booking;
        });

        return response()->json($bookings);
    }
}
