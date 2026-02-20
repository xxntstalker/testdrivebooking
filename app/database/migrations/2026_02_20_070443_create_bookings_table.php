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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->timestamps();

            // ГЛАВНОЕ: Уникальный индекс.
            // Физически запретит БД создать две брони на одну машину в одно время.
            $table->unique(['car_id', 'slot_id'], 'unique_car_slot');

            // Индекс для быстрого поиска броней по машине
            $table->index('car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
