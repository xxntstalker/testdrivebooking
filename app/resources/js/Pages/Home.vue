<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    cars: Array,
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Заголовок -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Запись на тест-драйв
                </h1>
                <p class="text-lg text-gray-600">
                    Выберите автомобиль и забронируйте удобное время
                </p>
            </div>

            <!-- Сетка карточек -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    v-for="car in cars"
                    :key="car.id"
                    class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
                >
                    <!-- Плейсхолдер вместо фото -->
                    <div class="h-48 bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                        <span class="text-white text-2xl font-bold">{{ car.model }}</span>
                    </div>

                    <!-- Информация -->
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            {{ car.model }}
                        </h3>
                        <p class="text-gray-600 mb-4">
                            VIN: {{ car.vin }}
                        </p>
                        <p class="text-sm text-gray-500 mb-6">
                            Номер: {{ car.license_plate }}
                        </p>

                        <!-- Кнопка -->
                        <Link
                            :href="`/booking?car_id=${car.id}`"
                            class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        >
                            Забронировать тест-драйв
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Если машин нет -->
            <div v-if="!cars || cars.length === 0" class="text-center text-gray-500">
                <p>Автомобили временно недоступны</p>
            </div>
        </div>
    </div>
</template>
