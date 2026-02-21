<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    cars: Array,
    slots: Array,
    selectedCarId: [String, Number, null],
});

const page = usePage();
const flashMessage = ref(page.props.flash?.success || null);

const form = ref({
    car_id: props.selectedCarId || '',
    slot_id: '',
    customer_name: '',
    customer_phone: '',
});

const slotStatus = ref({
    isChecking: false,
    isAvailable: null,
    message: '',
});

const submitStatus = ref({
    isSubmitting: false,
    error: null,
    success: null,
});

const showSuccessModal = ref(false);
const confirmedBooking = ref(null);

// Проверка занятости слота при изменении выбора
watch([() => form.value.car_id, () => form.value.slot_id], async ([carId, slotId]) => {
    if (!carId || !slotId) {
        slotStatus.value = { isChecking: false, isAvailable: null, message: '' };
        return;
    }

    slotStatus.value = { isChecking: true, isAvailable: null, message: 'Проверка...' };

    try {
        const response = await fetch(`/api/bookings/check?car_id=${carId}&slot_id=${slotId}`);
        const data = await response.json();

        slotStatus.value = {
            isChecking: false,
            isAvailable: data.available,
            message: data.available ? 'Слот свободен ✅' : 'Слот уже забронирован ❌',
        };
    } catch (error) {
        slotStatus.value = {
            isChecking: false,
            isAvailable: false,
            message: 'Ошибка проверки слота',
        };
    }
});

const submit = async () => {
    if (!slotStatus.value.isAvailable) {
        alert('Выбранный слот уже забронирован! Пожалуйста, выберите другое время.');
        return;
    }

    submitStatus.value = { isSubmitting: true, error: null };

    try {
        const response = await fetch('/api/bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (response.ok) {
            // Показываем успех
            confirmedBooking.value = data;
            showSuccessModal.value = true;

            // Сбрасываем состояние отправки
            submitStatus.value = { isSubmitting: false, error: null };

            // Сбрасываем форму
            form.value = {
                car_id: props.selectedCarId || '',
                slot_id: '',
                customer_name: '',
                customer_phone: '',
            };

            // Сбрасываем статус слота
            slotStatus.value = { isChecking: false, isAvailable: null, message: '' };
        } else if (response.status === 409) {
            // Конфликт (слот занят)
            submitStatus.value = {
                isSubmitting: false,
                error: 'Этот слот только что забронировали. Выберите другое время.',
            };
            // Обновить статус слота
            slotStatus.value = {
                isChecking: false,
                isAvailable: false,
                message: 'Слот только что забронирован ❌',
            };
        } else if (response.status === 422) {
            // Ошибка валидации
            submitStatus.value = {
                isSubmitting: false,
                error: 'Проверьте правильность заполнения полей.',
            };
        } else {
            // Другая ошибка
            submitStatus.value = {
                isSubmitting: false,
                error: data.message || 'Произошла ошибка при бронировании.',
            };
        }
    } catch (error) {
        submitStatus.value = {
            isSubmitting: false,
            error: 'Ошибка сети. Попробуйте позже.',
        };
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-2xl mx-auto px-4">
            <Link href="/" class="text-blue-600 hover:underline mb-6 inline-block">
                ← На главную
            </Link>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Запись на тест-драйв</h2>

                <!-- Сообщение об успехе из сессии -->
                <div v-if="flashMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ flashMessage }}
                </div>

                <!-- Ошибка отправки -->
                <div v-if="submitStatus.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ submitStatus.error }}
                </div>

                <form @submit.prevent="submit">
                    <!-- Автомобиль -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Автомобиль</label>
                        <select v-model="form.car_id" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Выберите авто</option>
                            <option v-for="car in cars" :key="car.id" :value="car.id">
                                {{ car.model }} ({{ car.license_plate }})
                            </option>
                        </select>
                    </div>

                    <!-- Слот времени -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Время</label>
                        <select v-model="form.slot_id" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Выберите слот</option>
                            <option v-for="slot in slots" :key="slot.id" :value="slot.id">
                                {{ slot.display }}
                            </option>
                        </select>

                        <!-- Статус проверки -->
                        <div v-if="slotStatus.message" class="mt-2 text-sm" :class="{
                            'text-yellow-600': slotStatus.isChecking,
                            'text-green-600': slotStatus.isAvailable === true,
                            'text-red-600': slotStatus.isAvailable === false,
                        }">
                            {{ slotStatus.message }}
                        </div>
                    </div>

                    <!-- Имя -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Имя</label>
                        <input v-model="form.customer_name" type="text" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required />
                    </div>

                    <!-- Телефон -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Телефон</label>
                        <input v-model="form.customer_phone" type="tel" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required />
                    </div>

                    <!-- Кнопка -->
                    <button
                        type="submit"
                        :disabled="!slotStatus.isAvailable || slotStatus.isChecking || submitStatus.isSubmitting"
                        class="w-full bg-green-600 text-white py-4 rounded-lg hover:bg-green-700 transition-colors font-medium disabled:bg-gray-400 disabled:cursor-not-allowed"
                    >
                        {{ submitStatus.isSubmitting ? 'Бронирование...' : 'Забронировать' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- Успешное бронирование -->
    <div v-if="submitStatus.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <h3 class="font-bold mb-2">✅ Бронь успешно создана!</h3>
        <p class="text-sm">
            <strong>Автомобиль:</strong> {{ submitStatus.success.car.model }}<br>
            <strong>Время:</strong> {{ submitStatus.success.slot.start_time }} - {{ submitStatus.success.slot.end_time }}<br>
            <strong>Имя:</strong> {{ submitStatus.success.customer_name }}<br>
            <strong>Телефон:</strong> {{ submitStatus.success.customer_phone }}<br>
            <strong>Статус:</strong> {{ submitStatus.success.status }}
        </p>
        <button @click="submitStatus.success = null" class="mt-3 text-sm underline hover:no-underline">
            Закрыть
        </button>
    </div>

    <!-- Модальное окно успеха -->
    <div v-if="showSuccessModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative">
            <!-- Кнопка закрытия -->
            <button @click="showSuccessModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                ✕
            </button>

            <!-- Заголовок -->
            <h3 class="text-xl font-bold text-green-700 mb-4">✅ Бронь успешно оформлена!</h3>

            <!-- Детали (из реального API) -->
            <div v-if="confirmedBooking" class="space-y-3 text-sm">
                <p><strong>Автомобиль:</strong> {{ confirmedBooking.car.model }} ({{ confirmedBooking.car.license_plate }})</p>
                <p><strong>Время:</strong> {{ confirmedBooking.slot.start_time }} — {{ confirmedBooking.slot.end_time }}</p>
                <p><strong>Имя:</strong> {{ confirmedBooking.customer_name }}</p>
                <p><strong>Телефон:</strong> {{ confirmedBooking.customer_phone }}</p>
                <p><strong>Статус:</strong>
                    <span class="px-2 py-1 rounded" :class="{
                    'bg-yellow-100 text-yellow-800': confirmedBooking.status === 'pending',
                    'bg-green-100 text-green-800': confirmedBooking.status === 'confirmed',
                    'bg-red-100 text-red-800': confirmedBooking.status === 'cancelled',
                }">
                    {{ confirmedBooking.status }}
                </span>
                </p>
                <p class="text-xs text-gray-500 mt-4">
                    Номер брони: #{{ confirmedBooking.id }}
                </p>
            </div>

            <!-- Кнопки -->
            <div class="mt-6 flex gap-3">
                <button @click="showSuccessModal = false" class="flex-1 bg-gray-200 py-2 rounded hover:bg-gray-300">
                    Закрыть
                </button>
                <button @click="window.location.href='/'" class="flex-1 bg-green-600 text-white py-2 rounded hover:bg-green-700">
                    На главную
                </button>
            </div>
        </div>
    </div>

</template>
