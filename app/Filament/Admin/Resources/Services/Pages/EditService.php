<?php

namespace App\Filament\Admin\Resources\Services\Pages;

use App\Filament\Admin\Resources\Services\ServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Cancella')
                ->requiresConfirmation()
                ->modalHeading('Cancella servizio')
                ->modalDescription(
                    'Il servizio verrà rimosso dagli elenchi attivi. '
                    . 'I report storici resteranno conservati.'
                )
                ->modalSubmitActionLabel('Cancella')
                ->modalCancelActionLabel('Annulla'),

            RestoreAction::make()
                ->label('Ripristina')
                ->requiresConfirmation()
                ->modalHeading('Ripristina servizio')
                ->modalSubmitActionLabel('Ripristina')
                ->modalCancelActionLabel('Annulla'),
        ];
    }
}