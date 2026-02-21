<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Имя клиента')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view_bookings')
                    ->label('Бронирования')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => "Бронирования: {$record->customer_name}")
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->modalContent(fn ($record) => view('filament.modals.customer-bookings', [
                        'bookings' => \App\Models\Booking::where('customer_phone', $record->customer_phone)
                            ->with(['car', 'slot'])
                            ->orderByDesc('created_at')
                            ->get(),
                    ])),
            ]);
    }
}
