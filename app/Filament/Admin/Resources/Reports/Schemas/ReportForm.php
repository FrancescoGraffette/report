<?php

namespace App\Filament\Admin\Resources\Reports\Schemas;

use App\Models\Client;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('client_id')
                    ->label('Cliente')
                    ->options(function (?Report $record): array {
                        $options = Client::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();

                        if ($record !== null) {
                            $options[$record->client_id] = $record->client_name;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->disabled(fn (?Report $record): bool => $record !== null)
                    ->dehydrated()
                    ->validationMessages([
                        'required' => 'Seleziona un cliente.',
                        'in' => 'Seleziona un cliente disponibile.',
                    ])
                    ->afterStateUpdated(function (
                        Get $get,
                        Set $set,
                        Component $livewire,
                        Field $component
                    ): void {
                        $set(
                            'service_ids',
                            self::activeServiceIds($get('client_id'))
                        );

                        self::validateFields($livewire, [
                            $component->getStatePath(),
                            'data.service_ids',
                        ]);
                    }),
                DatePicker::make('report_date')
                    ->label('Data report')
                    ->native(false)
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->locale('it')
                    ->default(fn (): string => self::today()),
                Select::make('period_type')
                    ->label('Tipo di periodo')
                    ->options([
                        'date_range' => 'Intervallo date',
                        'text' => 'Campagna / altro',
                    ])
                    ->default('date_range')
                    ->required()
                    ->live()
                    ->validationMessages([
                        'required' => 'Seleziona il tipo di periodo.',
                        'in' => 'Seleziona un tipo di periodo valido.',
                    ])
                    ->afterStateUpdated(function (
                        Component $livewire,
                        Field $component
                    ): void {
                        $livewire->resetValidation([
                            'data.period_start',
                            'data.period_end',
                            'data.period_label',
                        ]);

                        self::validateFields($livewire, [
                            $component->getStatePath(),
                        ]);
                    })
                    ->columnSpanFull(),

                DatePicker::make('period_start')
                    ->label('Dal')
                    ->native(false)
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->locale('it')
                    ->maxDate(fn (): string => self::today())
                    ->rules(fn (): array => [
                        'before_or_equal:' . self::today(),
                    ])
                    ->visible(fn (Get $get): bool =>
                        $get('period_type') === 'date_range')
                    ->required(fn (Get $get): bool =>
                        $get('period_type') === 'date_range')
                    ->live()
                    ->validationMessages([
                        'required' => 'Inserisci la data di inizio.',
                        'date' => 'Inserisci una data di inizio valida.',
                        'date_format' => 'Inserisci una data di inizio valida.',
                        'before_or_equal' =>
                            'La data di inizio deve essere oggi o un giorno precedente.',
                    ])
                    ->afterStateUpdated(function (
                        Get $get,
                        Set $set,
                        Component $livewire,
                        Field $component
                    ): void {
                        $start = $get('period_start');
                        $end = $get('period_end');

                        if (
                            filled($start) &&
                            filled($end) &&
                            $end < $start
                        ) {
                            $set('period_end', null);

                            $livewire->resetValidation('data.period_end');
                        } elseif (filled($end)) {
                            self::validateFields($livewire, [
                                'data.period_end',
                            ]);
                        }

                        self::validateFields($livewire, [
                            $component->getStatePath(),
                        ]);
                    }),

                DatePicker::make('period_end')
                    ->label('Al')
                    ->native(false)
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->locale('it')
                    ->minDate(fn (Get $get): ?string =>
                        $get('period_start') ?: null)
                    ->maxDate(fn (): string => self::today())
                    ->rules(fn (): array => [
                        'before_or_equal:' . self::today(),
                    ])
                    ->afterOrEqual('period_start')
                    ->visible(fn (Get $get): bool =>
                        $get('period_type') === 'date_range')
                    ->required(fn (Get $get): bool =>
                        $get('period_type') === 'date_range')
                    ->live()
                    ->validationMessages([
                        'required' => 'Inserisci la data di fine.',
                        'date' => 'Inserisci una data di fine valida.',
                        'date_format' => 'Inserisci una data di fine valida.',
                        'after_or_equal' =>
                            'La data di fine deve essere uguale o successiva alla data di inizio.',
                        'before_or_equal' =>
                            'La data di fine deve essere oggi o un giorno precedente.',
                    ])
                    ->afterStateUpdated(function (
                        Component $livewire,
                        Field $component
                    ): void {
                        self::validateFields($livewire, [
                            $component->getStatePath(),
                        ]);
                    }),

                TextInput::make('period_label')
                    ->label('Periodo / Campagna')
                    ->placeholder('Es. Promo estate')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool =>
                        $get('period_type') === 'text')
                    ->required(fn (Get $get): bool =>
                        $get('period_type') === 'text')
                    ->live(debounce: 400)
                    ->validationMessages([
                        'required' => 'Inserisci il periodo o la campagna.',
                        'max' =>
                            'Il periodo o la campagna non può superare 255 caratteri.',
                    ])
                    ->afterStateUpdated(function (
                        Component $livewire,
                        Field $component
                    ): void {
                        self::validateFields($livewire, [
                            $component->getStatePath(),
                        ]);
                    })
                    ->columnSpanFull(),

                Select::make('service_ids')
                    ->label('Servizi')
                    ->options(fn (Get $get, ?Report $record): array =>
                        self::serviceOptions($get('client_id'), $record))
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->minItems(1)
                    ->disabled(fn (Get $get): bool => ! $get('client_id'))
                    ->live()
                    ->helperText('Puoi rimuovere i servizi non inclusi nel report.')
                    ->validationMessages([
                        'required' => 'Seleziona almeno un servizio.',
                        'min' => 'Seleziona almeno un servizio.',
                        'array' => 'Seleziona i servizi dall’elenco.',
                        'in' => 'Seleziona soltanto servizi disponibili.',
                    ])
                    ->afterStateUpdated(function (
                        Component $livewire,
                        Field $component
                    ): void {
                        self::validateFields($livewire, [
                            $component->getStatePath(),
                        ]);
                    })
                    ->hintAction(
                        Action::make('addAllServices')
                            ->label('Aggiungi tutti')
                            ->disabled(fn (Get $get): bool =>
                                ! $get('client_id'))
                            ->action(function (
                                Get $get,
                                Set $set,
                                Component $livewire
                            ): void {
                                $set('service_ids', array_values(array_unique([
                                    ...($get('service_ids') ?? []),
                                    ...self::activeServiceIds($get('client_id')),
                                ])));

                                self::validateFields($livewire, [
                                    'data.service_ids',
                                ]);
                            })
                    )
                    ->columnSpanFull(),

                Repeater::make('document_links')
                    ->label('Link documenti')
                    ->schema([
                        TextInput::make('label')
                            ->label('Etichetta')
                            ->placeholder('Es. Report o Dashboard')
                            ->maxLength(255)
                            ->live(debounce: 400)
                            ->validationMessages([
                                'max' =>
                                    'L’etichetta non può superare 255 caratteri.',
                            ])
                            ->afterStateUpdated(function (
                                Component $livewire,
                                Field $component
                            ): void {
                                self::validateFields($livewire, [
                                    $component->getStatePath(),
                                ]);
                            }),

                        TextInput::make('url')
                            ->label('Link')
                            ->url()
                            ->rules(['url:http,https'])
                            ->required()
                            ->maxLength(2048)
                            ->live(debounce: 400)
                            ->validationMessages([
                                'required' =>
                                    'Inserisci il link del documento oppure rimuovi questa voce.',
                                'url' =>
                                    'Inserisci un link valido che inizi con http:// o https://.',
                                'max' =>
                                    'Il link non può superare 2048 caratteri.',
                            ])
                            ->afterStateUpdated(function (
                                Component $livewire,
                                Field $component
                            ): void {
                                self::validateFields($livewire, [
                                    $component->getStatePath(),
                                ]);
                            }),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Aggiungi link')
                    ->reorderable(false)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Note')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    private static function validateFields(
        Component $livewire,
        array $paths
    ): void {
        foreach ($paths as $path) {
            try {
                $livewire->validateOnly($path);
            } catch (ValidationException) {
                // Livewire mantiene il messaggio del campo ancora non valido.
                // Proseguiamo per rivalidare anche gli altri campi aggiornati.
            }
        }
    }

    public static function today(): string
    {
        return now('Europe/Rome')->toDateString();
    }

    public static function activeServiceIds(mixed $clientId): array
    {
        $client = Client::find($clientId);

        return $client?->services()
            ->orderBy('services.name')
            ->pluck('services.id')
            ->map(fn ($id): string => (string) $id)
            ->all() ?? [];
    }

    public static function serviceOptions(
        mixed $clientId,
        ?Report $record = null
    ): array {
        $client = Client::find($clientId);

        $options = $client?->services()
            ->orderBy('services.name')
            ->pluck('services.name', 'services.id')
            ->all() ?? [];

        foreach ($record?->services ?? [] as $service) {
            $options[$service['id']] = $service['name'];
        }

        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    public static function prepareData(
        array $data,
        ?Report $record = null
    ): array {
        $clientId = $record?->client_id ?? ($data['client_id'] ?? null);

        if ($record === null) {
            $client = Client::find($clientId);

            if ($client === null) {
                throw ValidationException::withMessages([
                    'data.client_id' => 'Seleziona un cliente attivo.',
                ]);
            }

            $data['client_name'] = $client->name;
        } else {
            $data['client_name'] = $record->client_name;
        }

        $options = self::serviceOptions($clientId, $record);

        $selectedIds = array_values(array_unique(
            $data['service_ids'] ?? []
        ));

        if ($selectedIds === []) {
            throw ValidationException::withMessages([
                'data.service_ids' => 'Seleziona almeno un servizio.',
            ]);
        }

        $snapshots = [];

        foreach ($selectedIds as $id) {
            if (! array_key_exists($id, $options)) {
                throw ValidationException::withMessages([
                    'data.service_ids' =>
                        'Uno dei servizi non è più disponibile. Rivedi la selezione.',
                ]);
            }

            $snapshots[] = [
                'id' => (int) $id,
                'name' => $options[$id],
            ];
        }

        $data['client_id'] = $clientId;
        $data['services'] = $snapshots;

        unset($data['service_ids']);

        if ($data['period_type'] === 'date_range') {
            $data['period_label'] = null;
        } else {
            $data['period_start'] = null;
            $data['period_end'] = null;
            $data['period_label'] = trim($data['period_label'] ?? '');

            if ($data['period_label'] === '') {
                throw ValidationException::withMessages([
                    'data.period_label' => 'Inserisci il periodo o la campagna.',
                ]);
            }
        }

        return $data;
    }
}