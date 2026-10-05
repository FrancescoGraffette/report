<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}