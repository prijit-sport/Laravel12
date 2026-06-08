<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class UsStock extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'exchange',
        'type',
    ];
}

