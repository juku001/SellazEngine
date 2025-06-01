<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BikerReturn extends Model
{
    

        protected $fillable = [
        'order_item_id',
        'quantity_returned',
        'reason',
        'returned_at'
    ];
}
