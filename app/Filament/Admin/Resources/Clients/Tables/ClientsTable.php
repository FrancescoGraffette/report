<?php

namespace App\Filament\Admin\Resources\Clients\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->searchPlaceholder('Cerca cliente')
            ->emptyStateHeading('Nessun cliente')
            ->emptyStateDescription('Aggiungi un cliente per iniziare.')
            ->columns([
                TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('services.name')
                    ->label('Servizi attivi')
                    ->badge()
                    ->placeholder('Nessun servizio attivo'),
            ])
            ->filters([])
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
                    ->modalHeading('Cancella cliente')
                    ->modalDescription(
                        'Il cliente verrà rimosso dagli elenchi attivi. '
                        . 'I report storici resteranno conservati.'
                    )
                    ->modalSubmitActionLabel('Cancella')
                    ->modalCancelActionLabel('Annulla'),
            ])
            ->toolbarActions([]);
    }
}