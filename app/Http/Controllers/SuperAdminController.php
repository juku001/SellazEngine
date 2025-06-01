<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;




class SuperAdminController extends Controller
{

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



    public function superBikers(int $superdealerId)
    {
        $user = User::find($superdealerId)->where('role', 'super_dealer')->first();
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
