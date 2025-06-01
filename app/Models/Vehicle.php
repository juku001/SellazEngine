<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'biker_id',
        'plate_number',
        'type',
        'brand'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'biker_id');
    }
}
