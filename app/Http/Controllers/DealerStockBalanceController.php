<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\SuperDealerItem;
use App\Models\SuperDealerOrder;
use App\Models\SuperDealerStock;
use Illuminate\Http\Request;

class DealerStockBalanceController extends Controller
{

    /**
     * Get stock balance for a super dealer.
     *
     * @OA\Get(
     *     path="/stock/balance",
     *     summary="Super dealer stock balance",
     *     tags={"Stock"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="ID of the super dealer (optional, defaults to authenticated user)",
     *         @OA\Schema(type="integer", example=105)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock balance retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Superdealer Balance"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="super_dealer_id", type="integer", example=105),
     *                     @OA\Property(property="product_id", type="integer", example=45),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=2500),
     *                     @OA\Property(property="quantity", type="integer", example=30),
     *                     @OA\Property(
     *                         property="products",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=45),
     *                         @OA\Property(property="name", type="string", example="Cigarette Pack"),
     *                         @OA\Property(property="company_id", type="integer", example=3),
     *                         @OA\Property(property="company_price", type="number", format="float", example=2500)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {

        $superDealerId = $request->query('id') ?? auth()->user()->id;
        $stocks = SuperDealerStock::with('products')->where('super_dealer_id', $superDealerId)->get();
        return ResponseHelper::success('Superdealer Balance', $stocks);

    }


        /**
     * Get all orders made by a super dealer.
     *
     * @OA\Get(
     *     path="/stock/orders",
     *     summary="List Super Dealer Orders",
     *     tags={"Stock"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="ID of the super dealer (optional, defaults to authenticated user)",
     *         @OA\Schema(type="integer", example=105)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of super dealer orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Superdealer Orders"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=11),
     *                     @OA\Property(property="company_id", type="integer", example=3),
     *                     @OA\Property(property="super_dealer_id", type="integer", example=105),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=50000),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="is_paid", type="boolean", example=false),
     *                     @OA\Property(property="date_to_pay", type="string", format="date-time", example="2025-06-20T00:00:00Z"),
     *                     @OA\Property(property="requested_at", type="string", format="date-time", example="2025-06-10T10:30:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function orders(Request $request)
    {

        $superDealerId = $request->query('id') ?? auth()->user()->id;
        $stocks = SuperDealerOrder::where('super_dealer_id', $superDealerId)->get();
        return ResponseHelper::success('Superdealer Orders', $stocks);

    }



        /**
     * Get items of a specific super dealer order.
     *
     * @OA\Get(
     *     path="/stock/orders/{orderId}/items",
     *     summary="List Items of a Super Dealer Order",
     *     tags={"Stock"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID of the Super Dealer Order",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of order items",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Superdealer Order Items"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=55),
     *                     @OA\Property(property="order_id", type="integer", example=12),
     *                     @OA\Property(property="product_id", type="integer", example=34),
     *                     @OA\Property(property="quantity", type="integer", example=100),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=1500)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found.")
     *         )
     *     )
     * )
     */

    public function items(Request $request, int $orderId)
    {
        $stocks = SuperDealerItem::
            where('order_id', $orderId)->get();
        return ResponseHelper::success('Superdealer Order Items', $stocks);

    }
}
