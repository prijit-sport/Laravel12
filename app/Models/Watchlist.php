<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $symbol
 * @property string|null $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Watchlist extends Model
{
    protected $fillable = ['symbol', 'note'];
}
