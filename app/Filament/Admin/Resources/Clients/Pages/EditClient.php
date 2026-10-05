<?php

namespace App\Filament\Admin\Resources\Clients\Pages;

use App\Filament\Admin\Resources\Clients\ClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Cancella')
                ->requiresConfirmation()
                ->modalHeading('Cancella cliente')
                ->modalDescription(
                    'Il cliente verrà rimosso dagli elenchi attivi. '
                    . 'I report storici resteranno conservati.'
                )
                ->modalSubmitActionLabel('Cancella')
                ->modalCancelActionLabel('Annulla'),

            RestoreAction::make()
                ->label('Ripristina')
                ->requiresConfirmation()
                ->modalHeading('Ripristina cliente')
                ->modalSubmitActionLabel('Ripristina')
                ->modalCancelActionLabel('Annulla'),
        ];
    }
}