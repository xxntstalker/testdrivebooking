<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Car;
use App\Models\TimeSlot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($slotId = request()->query('slot_id')) {
            $this->form->fill(['slot_id' => $slotId]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Время и автомобиль')
                    ->columns(2)
                    ->schema([
                        // Сначала слот
                        Select::make('slot_id')
                            ->label('Время бронирования')
                            ->options(fn () => TimeSlot::where('start_time', '>=', now())
                                ->orderBy('start_time')
                                ->get()
                                ->mapWithKeys(fn ($slot) => [
                                    $slot->id => $slot->start_time->format('d.m.Y H:i') . ' - ' . $slot->end_time->format('H:i')
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->live() // <-- важно для обновления машин
                            ->required()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('car_id', null)),

                        // Потом машина (зависит от слота)
                        Select::make('car_id')
                            ->label('Автомобиль')
                            ->options(function (callable $get) {
                                $slotId = $get('slot_id');
                                if (!$slotId) return [];

                                return Car::whereDoesntHave('bookings', function ($q) use ($slotId) {
                                    $q->where('slot_id', $slotId)
                                        ->where('status', '!=', 'cancelled');
                                })
                                    ->get()
                                    ->mapWithKeys(fn ($car) => [
                                        $car->id => "{$car->model} ({$car->license_plate})"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (callable $get) => !$get('slot_id'))
                            ->placeholder(fn (callable $get) =>
                            !$get('slot_id') ? 'Сначала выберите время' : 'Выберите автомобиль'
                            ),
                    ]),

                Section::make('Клиент')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Имя клиента')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('customer_phone')
                            ->label('Телефон')
                            ->required()
                            ->tel()
                            ->prefix('+7')
                            ->placeholder('999 123-45-67')
                            ->maxLength(20),
                    ]),
            ]);
    }
}
