<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingCheckController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'car_id' => 'required|integer|exists:cars,id',
            'slot_id' => 'required|integer|exists:time_slots,id',
        ]);

        $exists = Booking::where('car_id', $request->car_id)
            ->where('slot_id', $request->slot_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Slot is already booked' : 'Slot is available',
        ]);
    }
}
