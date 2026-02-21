<?php

namespace App\Exceptions;

use Exception;

class SlotAlreadyBookedException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report(): void
    {
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->message ?: 'This time slot is already booked for selected car',
            'error' => 'Conflict',
        ], $this->code ?: 409);
    }
}
