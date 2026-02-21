<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $cars = [
            ['model' => 'Tesla Model 3', 'vin' => '5YJ3E1EA1KF000001', 'license_plate' => 'A001AA'],
            ['model' => 'BMW X5', 'vin' => '5UXCR6C0XKLL00002', 'license_plate' => 'B002BB'],
            ['model' => 'Mercedes-Benz E-Class', 'vin' => 'WDD2130001A000003', 'license_plate' => 'C003CC'],
            ['model' => 'Audi A6', 'vin' => 'WAUZZZ4G0KN000004', 'license_plate' => 'D004DD'],
        ];

        foreach ($cars as $car) {
            Car::updateOrCreate(
                ['vin' => $car['vin']],
                $car
            );
        }
    }
}
