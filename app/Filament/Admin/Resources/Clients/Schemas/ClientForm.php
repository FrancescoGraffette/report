<?php

namespace App\Filament\Admin\Resources\Clients\Schemas;

use App\Models\Client;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome cliente')
                    ->required()
                    ->maxLength(255)
                    ->rules([
                        fn (?Client $record): Closure =>
                            function (
                                string $attribute,
                                mixed $value,
                                Closure $fail
                            ) use ($record): void {
                                $name = trim((string) $value);

                                if ($name === '') {
                                    $fail('Inserisci il nome del cliente.');

                                    return;
                                }

                                $query = Client::withTrashed()
                                    ->where(
                                        'normalized_name',
                                        mb_strtolower($name)
                                    );

                                if ($record !== null) {
                                    $query->where('id', '!=', $record->getKey());
                                }

                                if ($query->exists()) {
                                    $fail('Cliente già presente.');
                                }
                            },
                    ]),

                Select::make('services')
                    ->label('Servizi attivi')
                    ->relationship(
                        name: 'services',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder =>
                            $query->orderBy('name')
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->minItems(1)
                    ->validationMessages([
                        'required' => 'Seleziona almeno un servizio.',
                        'min' => 'Seleziona almeno un servizio.',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}