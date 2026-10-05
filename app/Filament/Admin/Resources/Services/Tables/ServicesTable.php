<?php

namespace App\Filament\Admin\Resources\Services\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome servizio')
                    ->searchable()
                    ->sortable(),
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
                    ->modalHeading('Cancella servizio')
                    ->modalDescription(
                        'Il servizio verrà rimosso dagli elenchi attivi. '
                        . 'I report storici resteranno conservati.'
                    )
                    ->modalSubmitActionLabel('Cancella')
                    ->modalCancelActionLabel('Annulla'),
            ])
            ->toolbarActions([]);
    }
}