<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();
        return ResponseHelper::success("List of all companies", $companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'abbr' => 'nullable|string',
            'description' => 'required|string',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error(
                'Failed to validate fields',
                $validator->errors(),
                422
            );
        }



        try {
            $company = new Company();
            $company->name = $request->name;
            $company->abbr = $request->abbr;
            $company->description = $request->description;
            // Handle optional image upload
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('companies'), $imageName);
                $company->logo = "companies/" . $imageName;
            }
            if ($company->save()) {
                return ResponseHelper::success('Company added successful.', $company, 201);
            } else {
                return ResponseHelper::success('Failed to add company.', [], 500);
            }
        } catch (Exception $e) {
            return ResponseHelper::success('Error : ' . $e, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $companyId)
    {
        $company = Company::find($companyId);
        if ($company) {
            return ResponseHelper::success(
                'Company found successful.',
                $company
            );
        }

        return ResponseHelper::error(
            'Company not found',
            [],
            404
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'abbr' => 'required|string|max:10',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation errors', $validator->errors(), 422);
        }
        $company = Company::find($id);
        if (!$company) {
            return ResponseHelper::error(
                'Company not found',
                [],
                404
            );
        }
        $company->update($request->only(['name', 'abbr', 'description']));

        return ResponseHelper::success(
            'Company updated successfully.',
            $company,
            201
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::find($id);
        if ($company) {

            $company->delete();
            return ResponseHelper::success(
                'Company deleted successful.',
                []
            );
        }
        return ResponseHelper::error(
            'Company not found',
            [],
            404
        );
    }
}
