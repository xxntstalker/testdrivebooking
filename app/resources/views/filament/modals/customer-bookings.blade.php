<div class="space-y-4">
    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
        Бронирования клиента
    </h4>

    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Автомобиль
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Дата и время
                </th>
                <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Статус
                </th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Действие
                </th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($bookings as $booking)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center
                                    {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-600' : '' }}
                                    {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-600' : '' }}
                                    {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-600' : '' }}">
                                <x-filament::icon icon="heroicon-o-truck" class="h-4 w-4" />
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $booking->car->model }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $booking->car->license_plate }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">
                            {{ $booking->slot->start_time->format('d.m.Y') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $booking->slot->start_time->format('H:i') }} – {{ $booking->slot->end_time->format('H:i') }}
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ match($booking->status) {
                                    'confirmed' => 'Подтверждено',
                                    'cancelled' => 'Отменено',
                                    default => 'Ожидание',
                                } }}
                            </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ \App\Filament\Resources\Bookings\BookingResource::getUrl('edit', ['record' => $booking]) }}"
                           target="_blank"
                           class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                            Открыть →
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Нет бронирований
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
