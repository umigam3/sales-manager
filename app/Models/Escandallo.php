<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escandallo extends Model
{
    protected $fillable = ['name', 'food_cost'];

    public function saleLines()
    {
        return $this->hasMany(SaleLine::class);
    }
}