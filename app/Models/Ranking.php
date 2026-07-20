<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    protected $fillable = ['gerai_id', 'periode_label', 'rank', 'total'];

    public function gerai()
    {
        return $this->belongsTo(Gerai::class);
    }
}
