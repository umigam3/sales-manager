<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['date', 'sale_number'];

    public function lines()
    {
        return $this->hasMany(SaleLine::class);
    }
}