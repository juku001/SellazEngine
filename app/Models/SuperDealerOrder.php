<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperDealerOrder extends Model
{
    protected $fillable = [
        'company_id',
        'super_dealer_id',
        'name',
        'brand',
        'total_amount',
        'company_price',
        'date_to_pay'

    ];
}
