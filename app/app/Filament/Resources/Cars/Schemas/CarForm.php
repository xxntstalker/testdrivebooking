<?php

namespace App\Filament\Resources\Cars\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model')
                    ->required(),
                TextInput::make('vin')
                    ->required(),
                TextInput::make('license_plate')
                    ->required(),
            ]);
    }
}
