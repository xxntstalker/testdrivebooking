<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $fillable = ['model', 'vin', 'license_plate'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
