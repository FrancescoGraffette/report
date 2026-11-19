<?php

namespace App\Filament\Admin\Resources\Services\Pages;

use App\Filament\Admin\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $name = trim($data['name']);

        $service = Service::onlyTrashed()
            ->where('normalized_name', mb_strtolower($name))
            ->first();

        if ($service !== null) {
            $service->fill(['name' => $name]);
            $service->restore();

            return $service;
        }

        return Service::create(['name' => $name]);
    }

    protected function getRedirectUrl(): string
    {
        return ServiceResource::getUrl('index');
    }

}