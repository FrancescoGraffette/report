<?php

namespace App\Filament\Admin\Resources\Reports\Pages;

use App\Filament\Admin\Resources\Reports\ReportResource;
use App\Filament\Admin\Resources\Reports\Schemas\ReportForm;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Aggiungi report';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Inserisci');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = ReportForm::prepareData($data);

        $data['status'] = 'todo';
        $data['viewed'] = false;
        $data['is_copy'] = false;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Report inserito';
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