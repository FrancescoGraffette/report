<?php

namespace App\Filament\Admin\Resources\Clients\Schemas;

use App\Models\Client;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;

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

                                $query = Client::query()
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
                    ->hintAction(
                        Action::make('addAllServices')
                            ->label('Aggiungi tutti')
                            ->action(function (Set $set): void {
                                $set('services', Service::query()
                                    ->orderBy('name')
                                    ->pluck('id')
                                    ->map(fn ($id): string => (string) $id)
                                    ->all());
                            })
                    )
                    ->columnSpanFull(),
            ]);
    }
}