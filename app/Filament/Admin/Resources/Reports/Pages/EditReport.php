<?php

namespace App\Filament\Admin\Resources\Reports\Pages;

use App\Filament\Admin\Resources\Reports\ReportResource;
use App\Filament\Admin\Resources\Reports\Schemas\ReportForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    public function getTitle(): string
    {
        return 'Modifica report';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Aggiorna');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['service_ids'] = array_map(
            fn (array $service): string => (string) $service['id'],
            $data['services'] ?? []
        );

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ReportForm::prepareData($data, $this->record);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Report aggiornato';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Cancella')
                ->requiresConfirmation()
                ->modalHeading('Cancella report')
                ->modalDescription('Vuoi cancellare questo report?')
                ->modalSubmitActionLabel('Cancella')
                ->modalCancelActionLabel('Annulla'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ReportResource::getUrl('index');
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.reports.validation-footer', [
            'duplicateMessage' => $this->getErrorBag()
                ->first('data.duplicate_report'),
        ]);
    }

}