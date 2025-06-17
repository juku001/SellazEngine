<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SuperDealerOrder;
use App\Models\SuperDealerItem;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelper;

class DealerRequestController extends Controller
{




    /**
     * @OA\Get(
     *     path="/orders/request/{id}",
     *     tags={"Super Dealer"},
     *     summary="Get all Super Dealer Orders for a company",
     *     description="Returns a list of Super Dealer orders for the given company, including each order's company, super dealer, and the super dealer's company.",
     *     operationId="getCompanyOrders",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orders retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="name", type="string", example="Order #12"),
     *                     @OA\Property(property="brand", type="string", example="ABC"),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=450000),
     *                     @OA\Property(property="company_price", type="number", format="float", example=400000),
     *                     @OA\Property(property="date_to_pay", type="string", format="date", example="2025-07-15"),
     *                     @OA\Property(
     *                         property="company",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="EasyTrack Ltd")
     *                     ),
     *                     @OA\Property(
     *                         property="super_dealer",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=7),
     *                         @OA\Property(property="name", type="string", example="Jane Super"),
     *                         @OA\Property(
     *                             property="company",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="EasyTrack Ltd")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Company does not exist"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */


    public function index($id)
    {
        // 1.  Verify the company exists
        $company = Company::find($id);
        if (!$company) {
            return ResponseHelper::error('Company does not exist', [], 404);
        }

        // 2.  Pull all orders for this company and eager‑load relationships
        $orders = SuperDealerOrder::with([
            'company',                     // the order’s own company
            'superDealer.company'          // superDealer + superDealer->company
        ])
            ->where('company_id', $company->id)
            ->get();

        return ResponseHelper::success('Orders retrieved successfully.', $orders);
    }





    /**
     * Place a new order as a Super Dealer.
     *
     * @OA\Post(
     *     path="/orders/request",
     *     summary="Place an order request",
     *     tags={"Super Dealer"},
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
     *         response=403,
     *         description="Only super dealers can place orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only super dealers can place orders.")
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
