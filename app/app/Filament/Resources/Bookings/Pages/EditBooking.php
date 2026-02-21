<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Car;
use App\Models\TimeSlot;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Section::make('Информация о бронировании')
                    ->columns(2)
                    ->schema([
                        // ID бронирования (только для просмотра)
                        TextInput::make('id')
                            ->label('Номер брони')
                            ->disabled()
                            ->dehydrated(false),

                        // Статус (только для просмотра, меняется через кнопки)
                        TextInput::make('status')
                            ->label('Текущий статус')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'confirmed' => '✅ Подтверждено',
                                'cancelled' => '❌ Отменено',
                                default => '⏳ Ожидание',
                            })
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Автомобиль и время')
                    ->columns(2)
                    ->schema([
                        // Выбор машины
                        Select::make('car_id')
                            ->label('Автомобиль')
                            ->options(fn () => Car::all()->mapWithKeys(fn ($car) => [
                                $car->id => "{$car->model} ({$car->license_plate})"
                            ]))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Сбрасываем слот при смене машины
                                $set('slot_id', null);
                            }),

                        // Выбор слота (зависит от машины)
                        Select::make('slot_id')
                            ->label('Время бронирования')
                            ->options(function (callable $get) {
                                $carId = $get('car_id');

                                if (!$carId) {
                                    return [];
                                }

                                // Доступные слоты: свободные ИЛИ текущий слот этой брони
                                return TimeSlot::where(function ($query) use ($carId, $get) {
                                    // Свободные слоты (нет брони)
                                    $query->whereDoesntHave('bookings', function ($q) {
                                        $q->where('status', '!=', 'cancelled');
                                    });

                                    // ИЛИ текущий слот этой брони (чтобы можно было оставить тот же)
                                    $query->orWhereHas('bookings', function ($q) use ($get) {
                                        $q->where('id', $this->record?->id);
                                    });
                                })
                                    ->where('start_time', '>=', now())
                                    ->orderBy('start_time')
                                    ->get()
                                    ->mapWithKeys(function ($slot) {
                                        $date = $slot->start_time->format('d.m.Y');
                                        $time = $slot->start_time->format('H:i') . ' - ' . $slot->end_time->format('H:i');
                                        return [
                                            $slot->id => "{$date} | {$time}"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (callable $get) => !$get('car_id'))
                            ->placeholder(fn (callable $get) =>
                            !$get('car_id')
                                ? 'Сначала выберите автомобиль'
                                : 'Выберите время'
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('Подтвердить')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'confirmed')
                ->requiresConfirmation()
                ->modalHeading('Подтверждение бронирования')
                ->modalDescription('Вы уверены, что хотите подтвердить эту бронь?')
                ->modalSubmitActionLabel('Да, подтвердить')
                ->action(function () {
                    $this->record->update(['status' => 'confirmed']);

                    // TODO: Отправить уведомление клиенту о подтверждении бронирования
                    // - SMS через SMS-шлюз (Twilio/SMSC/...)
                    // - Email с деталями бронирования
                    // - Push-уведомление в мобильное приложение
                    // - Логирование в систему уведомлений

                    Notification::make()
                        ->title('Бронирование подтверждено')
                        ->success()
                        ->send();
                }),

            Action::make('cancel')
                ->label('Отменить')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status !== 'cancelled')
                ->requiresConfirmation()
                ->modalHeading('Отмена бронирования')
                ->modalDescription('Вы уверены, что хотите отменить эту бронь?')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);

                    // TODO: Отправить уведомление клиенту об отмене бронирования
                    // - SMS с причиной отмены (если указана)
                    // - Email с извинениями и предложением альтернатив

                    Notification::make()
                        ->title('Бронирование отменено')
                        ->danger()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
