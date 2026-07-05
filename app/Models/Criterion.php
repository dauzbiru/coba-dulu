<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criterion extends Model
{
    protected $fillable = ['item_id', 'description', 'sort'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
