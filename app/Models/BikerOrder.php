<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BikerOrder extends Model
{
    protected $fillable = [
        'super_dealer_id',
        'biker_id',
        'status',
        'total_amount',
        'received_at',
        'closed_at'
    ];
}
