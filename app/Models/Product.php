<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'id',
        'company_id',
        'name',
        'brand',
        'image',
        'company_price'
    ];

    public function superDealerStocks()
    {
        return $this->hasMany(SuperDealerStock::class, 'product_id');
    }
}
