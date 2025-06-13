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
     * @OA\Get(
     *     path="/companies",
     *     tags={"Company"},
     *     summary="List all companies in the system",
     *     description="Fetches a list of all registered companies.",
     *     operationId="getCompanies",
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of companies retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all companies"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Company")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $companies = Company::all();
        return ResponseHelper::success("List of all companies", $companies);
    }


    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/companies",
     *     tags={"Company"},
     *     summary="Create a new Company",
     *     description="Stores a new company with a name, optional abbreviation, description, and logo image.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description","logo"},
     *                 @OA\Property(property="name", type="string", example="EasyTrack Ltd"),
     *                 @OA\Property(property="abbr", type="string", example="ETL"),
     *                 @OA\Property(property="description", type="string", example="Leading distribution company."),
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 ),
     *                 @OA\Property(property="primary_color", type="string", example="#FF0000"),
     *                 @OA\Property(property="secondary_color", type="string", example="#00FF00"),
     *                 @OA\Property(property="background_color", type="string", example="#FFFFFF"),
     *                 @OA\Property(property="text_color", type="string", example="#000000"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company added successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="data", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to add company."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'abbr' => 'nullable|string',
            'description' => 'required|string',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string'
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


            $company->primary_color = $request->primary_color;
            $company->secondary_color = $request->secondary_color;
            $company->background_color = $request->background_color;
            $company->text_color = $request->text_color;

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

    /**
     * @OA\Get(
     *     path="/companies/{id}",
     *     tags={"Company"},
     *     summary="Get a single Company By ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Single Company details",
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
     *             example="POS Details"
     *           ),
     *           @OA\Property(
     *             property="code",
     *             type = "integer",
     *             example="200"
     *           ),
     *           @OA\Property(
     *             property="data",
     *             type="object",
     *             ref="#/components/schemas/Company"
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *       response=404, 
     *       description="Company not found.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Company not found."),
     *          @OA\Property(property="code", type="integer", example=404),
     *       )
     *     ),
     * )
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

    /**
     * @OA\Put(
     *     path="/companies/{id}",
     *     tags={"Company"},
     *     summary="Update Company details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\RequestBody(
     *   required=true,
     *   @OA\JsonContent(
     *     type="object",
     *     required={"name", "description"},
     *     @OA\Property(property="name", type="string", example="Sellaz Company"),
     *     @OA\Property(property="abbr", type="string", nullable=true, example="SCL"),
     *     @OA\Property(property="description", type="string", example="This is the best company ever.")
     *   )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Company updated",
     *         @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean", example=true),
     *           @OA\Property(property="code", type="integer", example=200),
     *           @OA\Property(property="message", type="string", example="Company updated successfully."),
     *           @OA\Property(
     *             property="name",
     *             type="string",
     *             ref="#/components/schemas/Company"
     *           )
     *         )
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     * )
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'abbr' => 'required|string|max:10',
            'description' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string'
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
        $company->update($request->only(['name', 'abbr', 'description', 'primary_color', 'secondary_color', 'background_color', 'text_color']));

        return ResponseHelper::success(
            'Company updated successfully.',
            $company,
            201
        );
    }


    /**
     * Remove the specified resource from storage.
     */


    /**
     * @OA\Delete(
     *     path="/companies/{id}",
     *     tags={"Company"},
     *     summary="Delete a Company",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *       response=204, 
     *       description="Company deleted successfully.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="Company deleted successfully."),
     *          @OA\Property(property="code", type="integer", example=204),
     *       )
     *     ),
     *     @OA\Response(
     *       response=404, 
     *       description="Company not found.",
     *       @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="message", type="string", example="Company not found."),
     *          @OA\Property(property="code", type="integer", example=404),
     *       )
     *     ),
     * )
     */
    public function destroy(string $id)
    {
        $company = Company::find($id);
        if ($company) {

            $company->delete();
            return ResponseHelper::success(
                'Company deleted successful.',
                [],
                204
            );
        }
        return ResponseHelper::error(
            'Company not found',
            [],
            404
        );
    }
}
