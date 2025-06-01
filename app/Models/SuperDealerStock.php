<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperDealerStock extends Model
{
    protected $fillable = [
        'super_dealer_id',
        "product_id",
        'unit_price',
        'quantity'
    ];


    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
