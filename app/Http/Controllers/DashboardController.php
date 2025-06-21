<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;
use App\Models\BikerSale;
use App\Models\SuperDealerSale;
use App\Models\Stock;
use App\Models\SuperDealerRequest;
use App\Models\BikerRequest;

class DashboardController extends Controller
{
 






public function app()
{
    $auth = auth()->user();
    $role = $auth->role;

    if ($role !== 'super_dealer' && $role !== 'biker') {
        return ResponseHelper::error('User not authorized', [], 403);
    }

    $today = Carbon::today();
    $totalSales = 0;
    $stockValue = 0;
    $requestCount = 0;
    $expectedPayments = 0;

    if ($role === 'super_dealer') {
        // Total sales by super dealer today
        $totalSales = SuperDealerSale::whereDate('created_at', $today)
            ->where('super_dealer_id', $auth->id)
            ->sum('amount');

        // // Current stock value (assuming a `Stock` model with quantity and price)
        // $stockItems = Stock::where('super_dealer_id', $auth->id)->get();
        // $stockValue = $stockItems->sum(function ($item) {
        //     return $item->quantity * $item->price;
        // });

        // // Pending requests to company
        // $requestCount = SuperDealerRequest::where('super_dealer_id', $auth->id)
        //     ->whereDate('created_at', $today)
        //     ->count();

        // // Expected payments today
        // $expectedPayments = SuperDealerSale::where('super_dealer_id', $auth->id)
        //     ->whereDate('created_at', $today)
        //     ->sum('expected_payment');
    }

    if ($role === 'biker') {
        // Total sales by biker today
        $totalSales = BikerSale::whereDate('created_at', $today)
            ->where('biker_id', $auth->id)
            ->sum('amount');

        // // Current stock value
        // $stockItems = Stock::where('biker_id', $auth->id)->get();
        // $stockValue = $stockItems->sum(function ($item) {
        //     return $item->quantity * $item->price;
        // });

        // // Pending requests to super dealer
        // $requestCount = BikerRequest::where('biker_id', $auth->id)
        //     ->whereDate('created_at', $today)
        //     ->count();

        // // Expected payments today
        // $expectedPayments = BikerSale::where('biker_id', $auth->id)
        //     ->whereDate('created_at', $today)
        //     ->sum('expected_payment');
    }

    $data = [
        'today_sales' => $totalSales,
        'stock_value' => $stockValue,
        'requests' => $requestCount,
        'expected_payments' => $expectedPayments
    ];

    return ResponseHelper::success('Dashboard information', $data);
}

}
