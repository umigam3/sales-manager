<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['sale_number', 'date'];

    public function lines()
    {
        return $this->hasMany(SaleLine::class);
    }
}