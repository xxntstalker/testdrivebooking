<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('model'); // Например: "BMW X5"
            $table->string('vin', 17)->unique(); // VIN всегда 17 символов
            $table->string('license_plate')->unique(); // Госномер
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index('model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
