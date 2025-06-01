<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BikerOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price'
    ];
}
