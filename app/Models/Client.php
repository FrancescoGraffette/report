<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected static function booted(): void
    {
        static::saving(function (Client $client): void {
            $client->name = trim($client->name);
            $client->normalized_name = mb_strtolower($client->name);
        });
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withTimestamps();
    }
}