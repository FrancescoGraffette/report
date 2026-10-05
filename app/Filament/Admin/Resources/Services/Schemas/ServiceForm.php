<?php

namespace App\Filament\Admin\Resources\Services\Schemas;

use App\Models\Service;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome servizio')
                    ->required()
                    ->maxLength(255)
                    ->rules([
                        fn (?Service $record): Closure =>
                            function (
                                string $attribute,
                                mixed $value,
                                Closure $fail
                            ) use ($record): void {
                                $name = trim((string) $value);

                                if ($name === '') {
                                    $fail('Inserisci il nome del servizio.');

                                    return;
                                }

                                $query = Service::withTrashed()
                                    ->where(
                                        'normalized_name',
                                        mb_strtolower($name)
                                    );

                                if ($record !== null) {
                                    $query->where('id', '!=', $record->getKey());
                                }

                                if ($query->exists()) {
                                    $fail('Servizio già presente.');
                                }
                            },
                    ]),
            ]);
    }
}