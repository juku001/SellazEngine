<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\BikerOrder;
use App\Models\BikerOrderItem;
use App\Models\Product;
use App\Models\SuperDealerStock;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Pest\Support\ExceptionTrace;
use Response;

class BikerOrderController extends Controller
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

    /**
     * Place a new order as a Super Dealer.
     *
     * @OA\Post(
     *     path="/biker/order/request",
     *     summary="Biker Place an order request",
     *     tags={"BikerOrders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"products"},
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "quantity"},
     *                     @OA\Property(
     *                         property="product_id",
     *                         type="integer",
     *                         example=101,
     *                         description="ID of the product"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="integer",
     *                         example=3,
     *                         description="Quantity of the product"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order placed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order placed successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_id", type="integer", example=123),
     *                 @OA\Property(property="total", type="number", format="float", example=15000.00)
     *             )
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
     *                 example={"products.0.product_id": {"Each product must have a product ID."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Order creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order creation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Product ID 999 not found for this company.")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ], [
            'products.required' => 'Please provide at least one product.',
            'products.array' => 'Products should be an array of product items.',
            'products.min' => 'You must add at least one product.',
            'products.*.product_id.required' => 'Each product must have a product ID.',
            'products.*.product_id.exists' => 'One or more selected products do not exist.',
            'products.*.quantity.required' => 'Each product must have a quantity.',
            'products.*.quantity.integer' => 'Quantity must be a whole number.',
            'products.*.quantity.min' => 'Quantity must be at least 1.'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed.', $validator->errors(), 422);
        }
        $user = $request->user();
        $userId = $user->id;
        $dealerId = $user->super_dealer_id;

        $productsInput = $request->input('products');
        $productIds = collect($productsInput)->pluck('product_id')->toArray();

        // Fetch all products from DB with their company prices
        $productsFromDB = SuperDealerStock::whereIn('product_id', $productIds)
            // ->where('company_id', $user->company_id)
            ->get()
            ->keyBy('product_id');

        $totalAmount = 0;

        DB::beginTransaction();
        try {
            // Calculate total amount using DB prices
            foreach ($productsInput as $product) {
                $dbProduct = $productsFromDB[$product['product_id']] ?? null;

                if (!$dbProduct) {
                    throw new Exception("Product ID {$product['product_id']} not found for this company.");
                }

                $totalAmount += $product['quantity'] * $dbProduct->unit_price;
            }

            // Create the order
            $order = BikerOrder::create([
                'super_dealer_id' => $dealerId,
                'biker_id' => $userId,
                'total_amount' => $totalAmount
            ]);

            // Add items to the order
            foreach ($productsInput as $product) {
                $dbProduct = $productsFromDB[$product['product_id']];
                BikerOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $dbProduct->unit_price,
                ]);
            }

            DB::commit();

            return ResponseHelper::success('Order placed successfully.', [
                'order_id' => $order->id,
                'total' => $totalAmount
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Order creation failed.', ['error' => $e->getMessage()], 500);
        }
    }









    // /**
    //  * Display the specified resource.
    //  */
    // public function show(BikerOrder $bikerOrder)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    /**
     * Activate a biker order and deduct stock.
     *
     * @OA\Put(
     *     path="/biker/order/activate/{id}",
     *     summary="Activate Biker Order",
     *     tags={"BikerOrders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the biker order to activate",
     *         required=true,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order activated and stock deducted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order activated and stock deducted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Order already active or closed / Insufficient stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order already active or closed.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or stock not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error while activating order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to activate order. Error: ...")
     *         )
     *     )
     * )
     */

    public function update(Request $request, int $id)
    {
        $bikerOrder = BikerOrder::find($id);

        if (!$bikerOrder) {
            return ResponseHelper::error('Order not found', [], 404);
        }

        if ($bikerOrder->status !== 'pending') {
            return ResponseHelper::error('Order already active or closed.', [], 400);
        }

        DB::beginTransaction();

        try {
            $bikerOrder->status = 'active';
            $bikerOrder->received_at = now();
            $bikerOrder->save();

            $bikerItems = BikerOrderItem::where('order_id', $bikerOrder->id)->get();

            foreach ($bikerItems as $item) {
                $stock = SuperDealerStock::where('super_dealer_id', $bikerOrder->super_dealer_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$stock) {
                    DB::rollBack();
                    return ResponseHelper::error("Stock not found for product ID {$item->product_id}", [], 404);
                }

                if ($stock->quantity < $item->quantity) {
                    DB::rollBack();
                    return ResponseHelper::error("Insufficient stock for product ID {$item->product_id}", [], 400);
                }

                $stock->quantity -= $item->quantity;
                $stock->save();
            }

            DB::commit();
            return ResponseHelper::success('Order activated and stock deducted successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error("Failed to activate order. Error: " . $e->getMessage(), [], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */


    /**
     * Delete a pending biker order.
     *
     * @OA\Delete(
     *     path="/biker/order/delete/{id}",
     *     summary="Delete a pending biker order",
     *     tags={"BikerOrders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the biker order to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order deleted successful.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete active order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Can not delete an active order.")
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
     *         description="Server error while deleting order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete, Error: ...")
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
        if ($bikerOrder->status != 'pending') {
            return ResponseHelper::error('Can not delete an active order.', [], 400);
        }

        try {
            $bikerOrder->delete();
            return ResponseHelper::success('Order deleted successful.');

        } catch (Exception $e) {
            return ResponseHelper::error("Failed to delete, Error: " . $e, [], 500);
        }
    }
}
