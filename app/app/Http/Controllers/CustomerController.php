<?php
namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\TimeSlot;
use App\Actions\Booking\BookTestDriveAction;
use App\DTO\BookingData;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function home()
    {
        $cars = Car::all();

        return Inertia::render('Home', [
            'cars' => $cars,
        ]);
    }

    public function booking(Request $request): \Inertia\Response
    {
        $cars = Car::all();

        // Форматируем слоты на бэкенде (без timezone конверсии)
        $slots = TimeSlot::where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time->format('Y-m-d H:i'), // ← Формат без timezone
                    'end_time' => $slot->end_time->format('H:i'),
                    'display' => $slot->start_time->format('d.m.Y H:i') . ' - ' . $slot->end_time->format('H:i'),
                ];
            });

        return Inertia::render('Booking', [
            'cars' => $cars,
            'slots' => $slots,
            'selectedCarId' => $request->query('car_id'),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'slot_id' => 'required|exists:time_slots,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        $action = new BookTestDriveAction();
        $action->execute(BookingData::fromRequest($validated));

        return redirect()->route('customer.home')->with('success', 'Бронь создана!');
    }
}
