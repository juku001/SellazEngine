<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperDealerSale extends Model
{

    protected $fillable = [
        'super_dealer_id',
        'product_id',
        'super_dealer_item_id',
        'customer_name',
        'customer_mobile',
        'amount',
        'sales_date'

    ];
}
