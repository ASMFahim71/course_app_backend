<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('avatar')
                    ->default(null),
                TextInput::make('type')
                    ->default(null),
                TextInput::make('open_id')
                    ->default(null),
                TextInput::make('token')
                    ->default(null),
                TextInput::make('age')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
