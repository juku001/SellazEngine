<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\BikerCommission;
use App\Models\BikerOrder;
use App\Models\BikerOrderItem;
use App\Models\BikerReturn;
use App\Models\BikerSale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class BikerSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function sell(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'order_item_id' => 'required|exists:biker_order_items,id',
            'location' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1'
        ], [
            'customer_name.required' => 'Please provide the customer name.',
            'order_item_id.required' => 'Order item ID is required.',
            'order_item_id.exists' => 'The specified order item does not exist.',
            'location.required' => 'Please provide the location of the sale.',
            'quantity.required' => 'Please provide quantity sold.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'You must sell at least one item.'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', $validator->errors(), 422);
        }

        $orderItem = BikerOrderItem::find($request->order_item_id);
        $orderedQty = $orderItem->quantity;
        $soldQty = BikerSale::where('order_item_id', $orderItem->id)->sum('quantity_sold');
        $returnedQty = BikerReturn::where('order_item_id', $orderItem->id)->sum('quantity_returned');
        $availableQty = $orderedQty - ($soldQty + $returnedQty);

        if ($request->quantity > $availableQty) {
            return ResponseHelper::error("You only have {$availableQty} items left to sell.", [], 400);
        }

        BikerSale::create([
            'order_item_id' => $orderItem->id,
            'quantity_sold' => $request->quantity,
            'customer_name' => $request->customer_name,
            'location' => $request->location,
            'sale_date' => now(),
        ]);

        return ResponseHelper::success("Sale recorded successfully.");
    }



    public function returnItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|exists:biker_order_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ], [
            'order_item_id.required' => 'Order item ID is required.',
            'order_item_id.exists' => 'The specified order item does not exist.',
            'quantity.required' => 'Please provide quantity to return.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'You must return at least one item.',
            'reason.max' => 'Reason must not exceed 255 characters.'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', $validator->errors(), 422);
        }

        $orderItem = BikerOrderItem::find($request->order_item_id);
        $orderedQty = $orderItem->quantity;

        $soldQty = BikerSale::where('order_item_id', $orderItem->id)->sum('quantity_sold');
        $returnedQty = BikerReturn::where('order_item_id', $orderItem->id)->sum('quantity');
        $availableQty = $orderedQty - ($soldQty + $returnedQty);

        if ($request->quantity > $availableQty) {
            return ResponseHelper::error("You can only return up to {$availableQty} items.", [], 400);
        }

        BikerReturn::create([
            'order_item_id' => $orderItem->id,
            'quantity' => $request->quantity,
            'reason' => $request->reason,
            'return_date' => now(),
        ]);

        return ResponseHelper::success("Items returned successfully.");
    }




    /**
 * Complete a biker order after reconciliation of sold and returned items.
 *
 * @OA\Post(
 *     path="/biker/order/complete/{id}",
 *     summary="Complete a biker order",
 *     tags={"BikerOrders"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the biker order to complete",
 *         required=true,
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Biker order completed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Biker order completed successfully with full reconciliation.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Reconciliation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Reconciliation failed for product ID 5. Missing 3 items.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order or items not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Order not found")
 *         )
 *     )
 * )
 */

    public function complete(int $id)
    {
        $bikerOrder = BikerOrder::find($id);

        if (!$bikerOrder) {
            return ResponseHelper::error('Order not found', [], 404);
        }

        $bikerItems = BikerOrderItem::where('order_id', $bikerOrder->id)->get();

        if ($bikerItems->isEmpty()) {
            return ResponseHelper::error('No items found in this biker order.', [], 404);
        }

        foreach ($bikerItems as $item) {
            $orderedQty = $item->quantity;
            $itemId = $item->id;

            $soldQty = BikerSale::where('order_item_id', $itemId)->sum('quantity_sold');

            $returnedQty = BikerReturn::where('order_item_id', $itemId)->sum('quantity_returned'); // assuming `biker_returns` table uses `order_item_id`

            $reconciledQty = $soldQty + $returnedQty;

            if ($reconciledQty < $orderedQty) {
                return ResponseHelper::error("Reconciliation failed for product ID {$item->product_id}. Missing " . ($orderedQty - $reconciledQty) . " items.", [], 400);
            }

            if ($reconciledQty > $orderedQty) {
                return ResponseHelper::error("Over reconciliation for product ID {$item->product_id}. Too many items recorded.", [], 400);
            }
        }

        // All items reconciled correctly
        $bikerOrder->status = 'completed';
        $bikerOrder->completed_at = now();
        $bikerOrder->save();

        return ResponseHelper::success('Biker order completed successfully with full reconciliation.');
    }




    /**
 * Close a completed biker order and record the commission.
 *
 * @OA\Post(
 *     path="/biker/order/close/{id}",
 *     summary="Close a biker order and record commission",
 *     tags={"BikerOrders"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the biker order to close",
 *         required=true,
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order closed and commission recorded successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Order closed and commission recorded."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="sales", type="number", format="float", example=1500.00),
 *                 @OA\Property(property="commission", type="number", format="float", example=225.00),
 *                 @OA\Property(property="biker_id", type="integer", example=45)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Order status invalid for closing",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Cannot close an order unless it is marked complete.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Order not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to close order: [error message]")
 *         )
 *     )
 * )
 */


    public function destroy(int $id)
    {
        $bikerOrder = BikerOrder::find($id);

        if (!$bikerOrder) {
            return ResponseHelper::error('Order not found', [], 404);
        }

        if ($bikerOrder->status !== 'complete') {
            return ResponseHelper::error('Cannot close an order unless it is marked complete.', [], 400);
        }

        DB::beginTransaction();
        try {
            // Close the order
            $bikerOrder->status = 'closed';
            $bikerOrder->save();

            // Get all sales related to the order
            $orderItemIds = BikerOrderItem::where('order_id', $bikerOrder->id)->pluck('id');
            $totalSales = BikerSale::whereIn('order_item_id', $orderItemIds)->sum(DB::raw('quantity_sold * unit_price'));

            // Calculate commission (15%)
            $commissionPercent = 15;
            $commissionAmount = round($totalSales * ($commissionPercent / 100), 2);

            // Record commission
            BikerCommission::create([
                'order_id' => $bikerOrder->id,
                'biker_id' => $bikerOrder->biker_id,
                'sales_amount' => $totalSales,
                'commission' => $commissionAmount,
                'percentage' => $commissionPercent,
            ]);

            DB::commit();

            return ResponseHelper::success('Order closed and commission recorded.', [
                'sales' => $totalSales,
                'commission' => $commissionAmount,
                'biker_id' => $bikerOrder->biker_id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to close order: ' . $e->getMessage(), [], 500);
        }
    }

}
