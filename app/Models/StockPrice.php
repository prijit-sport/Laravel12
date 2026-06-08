<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $symbol
 * @property \Illuminate\Support\Carbon $trade_date
 * @property float $close
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class StockPrice extends Model
{
    protected $fillable = [
        'symbol',
        'trade_date',
        'close',
    ];

    protected $casts = [
        'trade_date' => 'date',
        'close' => 'float',
    ];
}
