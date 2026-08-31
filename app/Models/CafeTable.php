<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    protected $fillable = [
        'code',
        'name',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }
}
