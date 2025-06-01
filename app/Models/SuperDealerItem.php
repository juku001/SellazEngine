<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperDealerItem extends Model
{
    protected $fillable =[
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        
    ];
}
