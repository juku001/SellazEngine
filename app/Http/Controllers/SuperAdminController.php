<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Auth;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;




class SuperAdminController extends Controller
{





    /**
     * @OA\Get(
     *     path="/superadmins",
     *     tags={"Super Admin"},
     *     summary="List all superadmins in the system",
     *     description="Fetches a list of all registered superadmins.",
     *     operationId="getsuperadmins",
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of superadmins retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all superadmins"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SuperAdmin")
     *             )
     *         )
     *     )
     * )
     */


    public function index()
    {
        $users = User::where('role', 'super_admin')->get();
        return ResponseHelper::success(
            'List of all super admins',
            $users
        );
    }






    /**
     * Get all super dealers of a specific company.
     *
     * @OA\Get(
     *     path="/companies/{id}/superdealers",
     *     summary="List super dealers by company",
     *     tags={"Super Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the company",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of super dealers by company",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of super dealers by company"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tanzania Co. Ltd"),
     *                 @OA\Property(property="super_dealers", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="John SuperDealer"),
     *                         @OA\Property(property="email", type="string", example="super@dealer.com"),
     *                         @OA\Property(property="role", type="string", example="super_dealer"),
     *                         @OA\Property(property="company_id", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Company does not exist.")
     *         )
     *     )
     * )
     */

    public function superdealers(int $companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return ResponseHelper::error('Company does not exist.', [], 404);
        }

        $superDealers = User::where('company_id', $company->id)
            ->where('role', 'super_dealer')
            ->get();

        $companyData = $company->toArray();
        $companyData['super_dealers'] = $superDealers;

        return ResponseHelper::success('List of super dealers by company', $companyData);
    }



    /**
     * Get all products of a specific company.
     *
     * @OA\Get(
     *     path="/companies/{id}/products",
     *     summary="List products by company",
     *     tags={"Super Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the company",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products by company",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of products by company"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tanzania Co. Ltd"),
     *                 @OA\Property(property="products", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="Product A"),
     *                         @OA\Property(property="company_id", type="integer", example=1),
     *                         @OA\Property(property="company_price", type="number", format="float", example=1200.50),
     *                         @OA\Property(property="unit", type="string", example="Box"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-10T08:30:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-10T08:30:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Company does not exist.")
     *         )
     *     )
     * )
     */

    public function products(int $companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return ResponseHelper::error('Company does not exist.', [], 404);
        }

        $products = Product::where('company_id', $company->id)
            ->get();

        $productData = $company->toArray();
        $productData['products'] = $products;

        return ResponseHelper::success('List of products by company', $productData);
    }





    /**
     * Get all bikers of a specific company.
     *
     * @OA\Get(
     *     path="/companies/{id}/bikers",
     *     summary="List bikers by company",
     *     tags={"Super Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the company",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of bikers by company",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of bikers by company"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tanzania Co. Ltd"),
     *                 @OA\Property(property="bikers", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="John SuperDealer"),
     *                         @OA\Property(property="email", type="string", example="super@dealer.com"),
     *                         @OA\Property(property="role", type="string", example="bikers"),
     *                         @OA\Property(property="company_id", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Company does not exist.")
     *         )
     *     )
     * )
     */

    public function bikers(int $companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return ResponseHelper::error('Company does not exist.', [], 404);
        }

        $bikers = User::where('company_id', $company->id)
            ->where('role', 'biker')
            ->get();

        $companyData = $company->toArray();
        $companyData['bikers'] = $bikers;

        return ResponseHelper::success('List of bikers by company', $companyData);
    }



    /**
     * Get all bikers under a specific super dealer.
     *
     * @OA\Get(
     *     path="/superdealers/{id}/bikers",
     *     summary="List bikers by super dealer",
     *     tags={"Super Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the super dealer",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of bikers by super dealer",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of bikers by super dealer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="name", type="string", example="Super Dealer John"),
     *                 @OA\Property(property="email", type="string", example="super@dealer.com"),
     *                 @OA\Property(property="role", type="string", example="super_dealer"),
     *                 @OA\Property(property="bikers", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=201),
     *                         @OA\Property(property="name", type="string", example="Biker Alex"),
     *                         @OA\Property(property="email", type="string", example="biker@delivery.com"),
     *                         @OA\Property(property="role", type="string", example="biker"),
     *                         @OA\Property(property="super_dealer_id", type="integer", example=101)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Super dealer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Super dealer does not exist.")
     *         )
     *     )
     * )
     */


    public function superBikers(int $superdealerId)
    {
        // $user = User::find($superdealerId)->where('role', 'super_dealer')->first();
        $user = User::where('id', $superdealerId)->where('role', 'super_dealer')->first();
        if (!$user) {
            return ResponseHelper::error('Super dealer does not exist.', [], 404);
        }
        $bikers = User::where('super_dealer_id', $user->id)
            ->where('role', 'biker')
            ->get();

        $superDealerData = $user->toArray();
        $superDealerData['bikers'] = $bikers;

        return ResponseHelper::success('List of bikers by super dealer', $superDealerData);
    }




    /**
     * @OA\Put(
     *     path="/superadmins/{id}",
     *     tags={"Super Admin"},
     *     summary="Update an existing Super Admin",
     *     description="Edits the profile of a super admin user.",
     *     operationId="update",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the super admin to update",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name",  type="string",  example="Jane Doe"),
     *             @OA\Property(property="email", type="string",  format="email",  example="jane@example.com"),
     *             @OA\Property(property="phone", type="string",  example="255713334455"),
     *             @OA\Property(property="password", type="string", format="password", example="newSecret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newSecret123"),
     *             @OA\Property(property="sex",   type="string",  enum={"male","female"}, example="female")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Super admin updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status",  type="boolean", example=true),
     *             @OA\Property(property="message", type="string",  example="Super admin updated successfully."),
     *             @OA\Property(property="code",    type="integer", example=200),
     *             @OA\Property(property="data",    ref="#/components/schemas/SuperAdmin")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Super admin not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status",  type="boolean", example=false),
     *             @OA\Property(property="message", type="string",  example="Super admin not found."),
     *             @OA\Property(property="code",    type="integer", example=404)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status",  type="boolean", example=false),
     *             @OA\Property(property="message", type="string",  example="Validation Error."),
     *             @OA\Property(property="code",    type="integer", example=422),
     *             @OA\Property(property="data",    type="object",  example={"email": {"The email has already been taken."}})
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        /** Only a logged‑in super‑admin may update other super‑admins */
        $authUser = Auth::user();
        if ($authUser->role !== 'super_admin') {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        /** Fetch the target super‑admin */
        $superadmin = User::where('role', 'super_admin')->find($id);
        if (!$superadmin) {
            return ResponseHelper::error('Super admin not found.', [], 404);
        }

        /** Validation rules (fields are optional but validated when present) */
        $rules = [
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $superadmin->id,
            'phone' => 'sometimes|required|regex:/^255\\d{9}$/|unique:users,phone,' . $superadmin->id,
            'password' => 'nullable|min:6|confirmed',
            'sex' => 'sometimes|required|in:male,female',
        ];
        $messages = [
            'phone.regex' => 'Mobile phone should start with 255',
            'sex.in' => 'Enter sex as male or female',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }

        /** Apply updates only to provided fields */
        if ($request->filled('name'))
            $superadmin->name = $request->name;
        if ($request->filled('email'))
            $superadmin->email = $request->email;
        if ($request->filled('phone'))
            $superadmin->phone = $request->phone;
        if ($request->filled('sex'))
            $superadmin->sex = $request->sex;

        if ($request->filled('password')) {
            $superadmin->password = Hash::make($request->password);
        }

        $superadmin->save();

        return ResponseHelper::success('Super admin updated successfully.', $superadmin);
    }
















    /**
     * Remove the specified resource from storage.
     */


    /**
     * @OA\Delete(
     *     path="/superadmins/{id}",
     *     tags={"Super Admin"},
     *     summary="Delete a Superadmin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *       response=204, 
     *       description="Superadmin deleted successfully.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="Superadmin deleted successfully."),
     *          @OA\Property(property="code", type="integer", example=204),
     *       )
     *     ),
     *     @OA\Response(
     *       response=404, 
     *       description="Superadmin not found.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Superadmin not found."),
     *          @OA\Property(property="code", type="integer", example=404),
     *       )
     *     ),
     *     @OA\Response(
     *       response=403, 
     *       description="Unauthorized access.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Unauthorized access."),
     *          @OA\Property(property="code", type="integer", example=403),
     *       )
     *     ),
     *     @OA\Response(
     *       response=400, 
     *       description="Bad Request",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Can't delete the primary super admin"),
     *          @OA\Property(property="code", type="integer", example=400),
     *       )
     *     ),
     * )
     */
    public function destroy($id)
    {
        $authUser = auth()->user();
        if ($authUser->role != 'super_admin') {
            return ResponseHelper::error('Unauthorized access.', [], 403);
        }

        $superadmin = User::where('role', 'super_admin')->where('id', $id)->first();

        if ($superadmin == null) {
            return ResponseHelper::error('Superadmin not found.', [], 404);
        }

        if ($superadmin->id == 1) {
            return ResponseHelper::error('Cannot delete the primary super admin.', [], 400);
        }
        $superadmin->delete();

        return ResponseHelper::success('Product deleted successfully', [], 204);
    }
}
