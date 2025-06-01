<?php



namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class ProductController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            'auth:sanctum',
            new Middleware('check.user_type:super_admin', only: ['store', 'update', 'destroy'])
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $products = Product::where('company_id', $companyId)->get();

        return ResponseHelper::success('List of products', $products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:50',
            'company_id' => 'required|exists:companies,id',
            'company_price' => 'required|numeric|min:0',
            'brand' => 'required|string|max:50' // e.g., 'carton', 'packet'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', $validator->errors(), 422);
        }

        $product = Product::create([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'brand' => $request->brand,
            'company_price' => $request->company_price,

        ]);

        return ResponseHelper::success('Product created successfully', $product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        return ResponseHelper::success('Product details', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'company_price' => 'sometimes|required|numeric|min:0',
            'brand' => 'sometimes|required|string|max:50'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', $validator->errors(), 422);
        }

        $product->update($request->only(['name', 'company_price', 'brand']));

        return ResponseHelper::success('Product updated successfully', $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        $product->delete();

        return ResponseHelper::success('Product deleted successfully');
    }
}
