<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['category_id', 'name', 'bobot', 'sort'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function criteria()
    {
        return $this->hasMany(Criterion::class)->orderBy('sort');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
