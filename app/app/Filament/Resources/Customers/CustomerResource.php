<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Booking;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?int $navigationSort = 999;
    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $recordTitleAttribute = 'Клиенты';

    protected static ?string $modelLabel = 'Клиенты';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-users';

    public static function table(Table $table): Table
    {
        return (new CustomersTable())->configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Просто уникальные клиенты по телефону
        return parent::getEloquentQuery()
            ->select(['id', 'customer_phone', 'customer_name'])
            ->distinct();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
        ];
    }
}
