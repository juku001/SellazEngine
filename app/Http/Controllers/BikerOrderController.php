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
