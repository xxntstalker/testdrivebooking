<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Widgets\BookingCalendarWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class CalendarBookings extends Page
{
    protected static string $resource = BookingResource::class;

    protected string $view = 'filament.resources.bookings.pages.calendar-bookings';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Календарь бронирований';

    protected static ?string $title = 'Календарь';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('Список')
                ->icon('heroicon-o-list-bullet')
                ->url(BookingResource::getUrl('index')),

            Action::make('create')
                ->label('Новое бронирование')
                ->icon('heroicon-o-plus')
                ->url(BookingResource::getUrl('create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookingCalendarWidget::class,
        ];
    }
}
