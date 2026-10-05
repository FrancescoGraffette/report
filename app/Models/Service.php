<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            $service->name = trim($service->name);
            $service->normalized_name = mb_strtolower($service->name);
        });
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)
            ->withTimestamps();
    }

}