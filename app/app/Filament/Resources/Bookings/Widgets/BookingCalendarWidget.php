<?php

namespace App\Filament\Resources\Bookings\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\TimeSlot;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class BookingCalendarWidget extends FullCalendarWidget
{
    // Уникальный id виджета (важно!)
    protected static string $widgetId = 'booking-calendar';

    /**
     * Динамическая загрузка событий (вызывается при смене месяца/вида)
     */

    public function fetchEvents(array $info): array
    {
        $events = [];

        // Получаем все слоты в выбранном диапазоне
        $slots = TimeSlot::whereBetween('start_time', [$info['start'], $info['end']])
            ->with(['bookings' => function ($query) {
                // Только активные брони
                $query->where('status', '!=', 'cancelled')
                    ->with('car');
            }])
            ->orderBy('start_time')
            ->get();

        foreach ($slots as $slot) {
            foreach ($slot->bookings as $booking) {
                // Слот занят — показываем бронирование
                $events[] = [
                    'id' => 'booking_' . $booking->id,
                    'title' => "{$booking->car->model} ({$booking->customer_name})",
                    'start' => $slot->start_time->toIso8601String(),
                    'end' => $slot->end_time->toIso8601String(),
                    'color' => match ($booking->status) {
                        'confirmed' => '#22c55e',
                        'pending' => '#f59e0b',
                        default => '#6b7280',
                    },
                    'url' => BookingResource::getUrl('edit', ['record' => $booking]),
                    'extendedProps' => [
                        'type' => 'booking',
                        'booking_id' => $booking->id,
                        'customer' => $booking->customer_name,
                        'phone' => $booking->customer_phone,
                        'status' => $booking->status,
                    ],
                ];
            }

            if ($slot->bookings->isEmpty()) {
                // Слот свободен — показываем как доступный
                $events[] = [
                    'id' => 'slot_' . $slot->id,
                    'title' => 'Свободно',
                    'start' => $slot->start_time->toIso8601String(),
                    'end' => $slot->end_time->toIso8601String(),
                    'color' => '#ffffff', // Светло-серый
                    'textColor' => '#6b7280',
                    'url' => BookingResource::getUrl('create', ['slot_id' => $slot->id]),
                    'extendedProps' => [
                        'type' => 'free_slot',
                        'slot_id' => $slot->id,
                    ],
                ];
            }
        }

        return $events;
    }

    /**
     * Настройки календаря
     */
    protected function getOptions(): array
    {
        return [
            'firstDay' => 1,
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'locale' => 'ru',
            'editable' => false,
            'selectable' => true,
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '20:00:00',
            'allDaySlot' => false,

            // ВАЖНО: Разрешить перекрывающиеся события
            'eventOverlap' => true, // События могут перекрываться

            // Отображение событий в колонке
            'slotEventOverlap' => true, // Показывать перекрытия

            // Или для timeGrid: разделить ширину поровну
            'eventDisplay' => 'block', // 'block', 'list-item', 'auto', 'background'

            // Минимальная высота события
            'eventMinHeight' => 30,

            // Для dayGrid: показывать "+X more" если много
            'dayMaxEvents' => true, // или число, например 3

            // Для timeGrid: разрешить стекинг
            'eventOrder' => 'start,-duration,allDay,title',
        ];
    }

    /**
     * Обработка клика по событию (опционально)
     */
    public function onEventClick(array $event): void
    {
        // Редирект на редактирование
        $this->redirect(route('filament.admin.resources.bookings.edit', ['record' => $event['id']]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
