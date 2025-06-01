<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BikerSale extends Model
{
    protected $fillable = [
        'order_item_id',
        'quantity_sold',
        'customer_name',
        'location',
        'sale_date'
    ];
}
