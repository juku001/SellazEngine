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
