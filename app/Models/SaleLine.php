<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleLine extends Model
{
    protected $fillable = ['sale_id', 'escandallo_id', 'quantity', 'price'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function escandallo()
    {
        return $this->belongsTo(Escandallo::class);
    }
}