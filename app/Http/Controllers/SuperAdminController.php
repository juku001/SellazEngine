<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;




class SuperAdminController extends Controller
{



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
}
