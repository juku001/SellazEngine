<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SuperDealerOrder;
use App\Models\SuperDealerItem;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelper;

class DealerRequestController extends Controller
{




    public function request(Request $request)
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
        if ($user->role !== 'super_dealer') {
            return ResponseHelper::error('Only super dealers can place orders.', [], 403);
        }

        $productsInput = $request->input('products');
        $productIds = collect($productsInput)->pluck('product_id')->toArray();

        // Fetch all products from DB with their company prices
        $productsFromDB = Product::whereIn('id', $productIds)
            ->where('company_id', $user->company_id)
            ->get()
            ->keyBy('id');



            // return $productsInput;
        $totalAmount = 0;

        DB::beginTransaction();
        try {
            // Calculate total amount using DB prices
            foreach ($productsInput as $product) {
                $dbProduct = $productsFromDB[$product['product_id']] ?? null;

                if (!$dbProduct) {
                    throw new \Exception("Product ID {$product['product_id']} not found for this company.");
                }

                $totalAmount += $product['quantity'] * $dbProduct->company_price;
            }

            // Create the order
            $order = SuperDealerOrder::create([
                'company_id' => $user->company_id,
                'super_dealer_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'is_paid' => false,
                'date_to_pay' => now()->addDays(7),
                'requested_at' => now(),
            ]);

            // Add items to the order
            foreach ($productsInput as $product) {
                $dbProduct = $productsFromDB[$product['product_id']];
                SuperDealerItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $dbProduct->company_price,
                ]);
            }

            DB::commit();

            return ResponseHelper::success('Order placed successfully.', [
                'order_id' => $order->id,
                'total' => $totalAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Order creation failed.', ['error' => $e->getMessage()], 500);
        }
    }


}
