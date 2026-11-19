<?php

namespace App\Filament\Admin\Resources\Clients\Pages;

use App\Filament\Admin\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Aggiungi cliente';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $name = trim($data['name']);

        $client = Client::onlyTrashed()
            ->where('normalized_name', mb_strtolower($name))
            ->first();

        if ($client !== null) {
            $client->fill(['name' => $name]);
            $client->restore();

            return $client;
        }

        return Client::create(['name' => $name]);
    }

    protected function getRedirectUrl(): string
    {
        return ClientResource::getUrl('index');
    }
}