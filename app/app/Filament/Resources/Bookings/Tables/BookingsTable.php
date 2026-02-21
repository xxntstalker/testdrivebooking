<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Машина: модель + номер
                TextColumn::make('car.model')
                    ->label('Автомобиль')
                    ->description(fn ($record) => $record->car?->license_plate)
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('car', function ($q) use ($search) {
                            $q->where('model', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                // Слот: дата и время
                TextColumn::make('slot.start_time')
                    ->label('Время бронирования')
                    ->dateTime('d.m.Y H:i')
                    ->description(fn ($record) => $record->slot?->end_time?->format('H:i') ? 'до ' . $record->slot->end_time->format('H:i') : null)
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Подтверждено',
                        'cancelled' => 'Отменено',
                        'pending' => 'Ожидание',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
