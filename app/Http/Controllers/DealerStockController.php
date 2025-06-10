<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\SuperDealerItem;
use App\Models\SuperDealerOrder;
use App\Models\SuperDealerStock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class DealerStockController extends Controller
{


    /**
     * Approve a Super Dealer order by ID.
     *
     * @OA\Post(
     *     path="/order/status",
     *     summary="Approve a Super Dealer order",
     *     tags={"Super Dealer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(
     *                 property="order_id",
     *                 type="integer",
     *                 example=123,
     *                 description="ID of the order to approve"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order approved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"order_id": {"The specified order does not exist."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error while updating order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update the order.")
     *         )
     *     )
     * )
     */

    public function status(Request $request)
    {
        $validator = Validator::make($request->only('order_id'), [
            'order_id' => 'required|exists:super_dealer_orders,id'
        ], [
            'order_id.required' => 'Please specify the order ID.',
            'order_id.exists' => 'The specified order does not exist.'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $id = $request->input('order_id');
            $order = SuperDealerOrder::find($id);

            if (!$order) {
                return ResponseHelper::error('Order not found.', [], 404);
            }

            $order->status = 'approved';

            if ($order->save()) {
                return ResponseHelper::success('Order approved successfully.');
            } else {
                return ResponseHelper::error('Failed to update the order.', [], 500);
            }

        } catch (Exception $e) {
            return ResponseHelper::error("Error changing order: " . $e->getMessage(), [], 500);
        }
    }





        /**
     * Fulfill an approved Super Dealer order and update their stock.
     *
     * @OA\Post(
     *     path="/order/fulfill",
     *     summary="Fulfill a Super Dealer order",
     *     tags={"Super Dealer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(
     *                 property="order_id",
     *                 type="integer",
     *                 example=123,
     *                 description="ID of the approved order to be fulfilled"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order fulfilled and stocks updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order fulfilled and stocks updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Order not approved yet",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Approve order first.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"order_id": {"The specified order does not exist."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error while fulfilling the order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error fulfilling order: Something went wrong.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->only('order_id'), [
            'order_id' => 'required|exists:super_dealer_orders,id'
        ], [
            'order_id.required' => 'Please specify the order ID.',
            'order_id.exists' => 'The specified order does not exist.'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $orderId = $request->order_id;
            $order = SuperDealerOrder::find($orderId);

            if (!$order) {
                return ResponseHelper::error('Order not found.', [], 404);
            }

            if ($order->status != 'approved') {
                return ResponseHelper::error('Approve order first.', [], 400);
            }

            $order->status = 'fulfilled';
            $order->date_to_pay = now()->addDays(7);

            if (!$order->save()) {
                DB::rollBack();
                return ResponseHelper::error('Failed to update the order.', [], 500);
            }

            $orderItems = SuperDealerItem::where('order_id', $orderId)->get();

            foreach ($orderItems as $item) {
                $stock = SuperDealerStock::where('super_dealer_id', $order->super_dealer_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($stock) {
                    // Update existing stock
                    $stock->quantity += $item->quantity;
                    // $stock->unit_price = $item->unit_price; // Optional: update price if needed
                    $stock->save();
                } else {
                    // Create new stock entry
                    SuperDealerStock::create([
                        'super_dealer_id' => $order->super_dealer_id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price
                    ]);
                }
            }

            DB::commit();

            return ResponseHelper::success('Order fulfilled and stocks updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error("Error fulfilling order: " . $e->getMessage(), [], 500);
        }
    }


}
