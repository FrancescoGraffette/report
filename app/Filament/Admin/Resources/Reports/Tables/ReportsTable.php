<?php

namespace App\Filament\Admin\Resources\Reports\Tables;

use App\Models\Client;
use App\Models\Report;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        $statuses = [
            'todo' => 'Da fare',
            'in_progress' => 'In lavorazione',
            'paused' => 'In pausa',
            'done' => 'Finito',
        ];

        return $table
            ->defaultSort(
                fn (Builder $query): Builder => $query
                    ->orderByDesc('report_date')
                    ->orderByDesc('id')
            )
            ->recordUrl(null)
            ->searchPlaceholder('Cerca cliente o campagna')
            ->emptyStateHeading('Nessun report')
            ->emptyStateDescription('Aggiungi un report per iniziare.')
            ->columns([
                TextColumn::make('report_date')
                    ->label('Data report')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_label')
                    ->label('Periodo / Campagna')
                    ->searchable()
                    ->getStateUsing(function (Report $record): string {
                        return $record->period_type === 'date_range'
                            ? ($record->period_start?->format('d/m/Y') ?? '—')
                                . ' – '
                                . ($record->period_end?->format('d/m/Y') ?? '—')
                            : ($record->period_label ?? '—');
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('service_names')
                    ->label('Servizi')
                    ->getStateUsing(
                        fn (Report $record): array =>
                            array_column($record->services ?? [], 'name')
                    )
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),

                SelectColumn::make('status')
                    ->label('Stato')
                    ->options($statuses)
                    ->rules(['required', 'in:todo,in_progress,paused,done'])
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('documents')
                    ->label('Documenti')
                    ->getStateUsing(function (Report $record): HtmlString {
                        $links = [];

                        foreach ($record->document_links ?? [] as $index => $document) {
                            $url = $document['url'] ?? '';

                            $scheme = strtolower(
                                (string) parse_url($url, PHP_URL_SCHEME)
                            );

                            if (
                                ! filter_var($url, FILTER_VALIDATE_URL)
                                || ! in_array($scheme, ['http', 'https'], true)
                            ) {
                                continue;
                            }

                            $label = trim($document['label'] ?? '');

                            if ($label === '') {
                                $label = 'Documento ' . ($index + 1);
                            }

                            $links[] = '<a href="' . e($url)
                                . '" target="_blank" rel="noopener noreferrer"'
                                . ' class="underline">'
                                . e($label) . '</a>';
                        }

                        return new HtmlString(
                            $links === [] ? '—' : implode('<br>', $links)
                        );
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                CheckboxColumn::make('viewed')
                    ->label('Visionato')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('notes')
                    ->label('Note')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->options(fn (): array => Client::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->placeholder('Tutti i clienti'),

                SelectFilter::make('status')
                    ->label('Stato')
                    ->options($statuses)
                    ->placeholder('Tutti gli stati'),
            ])
            ->recordActionsColumnLabel('Azioni')
            ->recordActions([
                EditAction::make()
                    ->label('Modifica')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip('Modifica'),

                DeleteAction::make()
                    ->label('Cancella')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->tooltip('Cancella')
                    ->requiresConfirmation()
                    ->modalHeading('Cancella report')
                    ->modalDescription('Vuoi cancellare questo report?')
                    ->modalSubmitActionLabel('Cancella')
                    ->modalCancelActionLabel('Annulla'),
            ])
            ->toolbarActions([]);
    }
}