<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\SuperDealerItem;
use App\Models\SuperDealerOrder;
use App\Models\SuperDealerStock;
use Illuminate\Http\Request;

class DealerStockBalanceController extends Controller
{
    public function index(Request $request)
    {

        $superDealerId = $request->query('id') ?? auth()->user()->id;
        $stocks = SuperDealerStock::with('products')->where('super_dealer_id', $superDealerId)->get();
        return ResponseHelper::success('Superdealer Balance', $stocks);

    }

    public function orders(Request $request)
    {

        $superDealerId = $request->query('id') ?? auth()->user()->id;
        $stocks = SuperDealerOrder::where('super_dealer_id', $superDealerId)->get();
        return ResponseHelper::success('Superdealer Orders', $stocks);

    }


    public function items(Request $request, int $orderId)
    {
        $stocks = SuperDealerItem::
        where('order_id', $orderId)->get();
        return ResponseHelper::success('Superdealer Order Items', $stocks);

    }
}
