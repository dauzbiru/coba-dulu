<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjelasanFormulir extends Model
{
    protected $table = 'penjelasan_formulir';

    protected $fillable = ['formulir', 'kondisi', 'penjelasan', 'sort'];
}
