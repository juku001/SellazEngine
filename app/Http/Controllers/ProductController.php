<?php



namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Product;
use Exception;
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

    /**
     * @OA\Get(
     *   tags={"Product"},
     *   path="/products",
     *   summary="Get list of products",
     *   description="Fetches a list of all products of that particular company",
     *   operationId="getProducts",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *         response=200,
     *         description="List of products retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all products"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     )
     * )
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


    /**
     * @OA\Post(
     *     path="/products",
     *     tags={"Product"},
     *     summary="Create a new Product",
     *     description="Stores a new Product with a name, brand, company_id, and product image.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "company_id","brand","image", "company_price"},
     *                 @OA\Property(property="name", type="string", example="Master Sports"),
     *                 @OA\Property(property="brand", type="string", example="SM"), 
     *                 @OA\Property(property="company_id", type="integer", example=1),
     *                 @OA\Property(property="company_price", type="integer", example=12000),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to add Product."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:50',
            'company_id' => 'required|exists:companies,id',
            'company_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'brand' => 'required|string|max:50' // e.g., 'carton', 'packet'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', $validator->errors(), 422);
        }



        try {

            $product = new Product();
            $product->company_id = $request->company_id;
            $product->name = $request->name;
            $product->brand = $request->brand;
            $product->company_price = $request->company_price;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('products'), $imageName);
                $product->logo = "products/" . $imageName;
            }

            if ($product->save()) {
                return ResponseHelper::success('Product added successful.', $product, 201);
            } else {
                return ResponseHelper::error('Failed to add Product.', [], 500);
            }

        } catch (Exception $e) {
            return ResponseHelper::error('Error : ' . $e, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */




    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     tags={"Product"},
     *     summary="Get a single Product By ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Single Product details",
     *         @OA\JsonContent(
     *           @OA\Property(
     *             property="status",
     *             type="boolean",
     *             example="true",
     *             description="true for success, false for fail"
     *           ),
     *           @OA\Property(
     *             property="message",
     *             type = "string",
     *             example="Product details"
     *           ),
     *           @OA\Property(
     *             property="code",
     *             type = "integer",
     *             example="200"
     *           ),
     *           @OA\Property(
     *             property="data",
     *             type="object",
     *             ref="#/components/schemas/Product"
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *       response=404, 
     *       description="Product not found.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Product not found."),
     *          @OA\Property(property="code", type="integer", example=404),
     *       )
     *     ),
     * )
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
    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     tags={"Product"},
     *     summary="Update Product details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "brand", "company_price"},
     *                 @OA\Property(property="name", type="string", example="Sellaz Product"),
     *                 @OA\Property(property="brand", type="string", example="Best Brand"),
     *                 @OA\Property(property="company_price", type="number", format="float", example=12000),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Product image file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Product updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized access."),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     * )
     */


    public function update(Request $request, Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'company_price' => 'sometimes|required|numeric|min:0',
            'brand' => 'sometimes|required|string|max:50',
            'image' => 'sometimes|required|file'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', $validator->errors(), 422);
        }

        // $product->update($request->only(['name', 'company_price', 'brand']));

        // return ResponseHelper::success('Product updated successfully', $product);
        if ($request->hasFile('image')) {
            $path = $request->file(key: 'image')->store('products', 'public');
            $product->image = $path;
        }

        $product->update($request->only(['name', 'company_price', 'brand']));
        $product->save(); // Ensure image change is saved

        return ResponseHelper::success('Product updated successfully', $product);

    }

    /**
     * Remove the specified resource from storage.
     */


    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     tags={"Product"},
     *     summary="Delete a Product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *       response=204, 
     *       description="Product deleted successfully.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="Product deleted successfully."),
     *          @OA\Property(property="code", type="integer", example=204),
     *       )
     *     ),
     *     @OA\Response(
     *       response=404, 
     *       description="Product not found.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Product not found."),
     *          @OA\Property(property="code", type="integer", example=404),
     *       )
     *     ),
     * )
     */
    public function destroy(Product $product)
    {

        if(!$product){
            return ResponseHelper::error('Product not found.', [], 404);
        }


        if ($product->company_id !== auth()->user()->company_id) {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        $product->delete();

        return ResponseHelper::success('Product deleted successfully', [], 204);
    }
}
