<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class RegistrationController extends Controller
{



    /**
     * @OA\Post(
     *     path="/register/superadmin",
     *     tags={"Registration"},
     *     summary="Register a Super Admin",
     *     description="This endpoint registers a new super admin user.",
     *     operationId="registerSuperAdmin",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "password", "password_confirmation", "sex"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="phone", type="string", example="255712345678"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
     *             @OA\Property(property="sex", type="string", enum={"male", "female"}, example="male")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Super admin registered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Super admin registered successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", ref="#/components/schemas/SuperAdmin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object", example={"phone": {"Mobile phone should start with 255"}})
     *         )
     *     )
     * )
     */

    public function super(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|regex:/^255\d{9}$/|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'sex' => 'required|in:male,female'
        ], [
            'phone.regex' => 'Mobile phone should start with 255',
            'sex.in' => 'Enter sex as male or female'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }

        $currentUserId = Auth::user()->id;
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'super_admin',
            'sex' => $request->sex,
            'created_by' => $currentUserId
        ]);

        return ResponseHelper::success('Super admin registered successfully.', $user);
    }






    /**
     * @OA\Post(
     *     path="/register/superdealer",
     *     tags={"Registration"},
     *     summary="Register a Super Dealer",
     *     description="This endpoint registers a new super dealer.",
     *     operationId="registerSuper Dealer",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "password", "password_confirmation", "sex", "company_id"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="phone", type="string", example="255712345678"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="location", type="string", nullable=true, example="Kisutu, Ilala"),
     *             @OA\Property(property="company_id", type="integer", example=1),
     *             @OA\Property(property="business_name", type="string", nullable=true, example="Mkuya Shop"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
     *             @OA\Property(property="sex", type="string", enum={"male", "female"}, example="male")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Super dealer registered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Super dealer registered successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", ref="#/components/schemas/OtherUsers")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object", example={"phone": {"Mobile phone should start with 255"}})
     *         )
     *     )
     * )
     */
    public function dealer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|regex:/^255\d{9}$/|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'sex' => 'nullable|in:male,female',
            'business_name' => 'nullable|string',
            'location' => 'nullable|string',
            'password' => 'required|min:6|confirmed',
            'company_id' => 'required|exists:companies,id',
        ], [
            'company_id.exists' => 'Company ID does not exist'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }

        $currentUserId = Auth::user()->id;
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'super_dealer',
            'sex' => $request->sex,
            'business_name' => $request->business_name,
            'location' => $request->location,
            'company_id' => $request->company_id,
            'created_by' => $currentUserId
        ]);

        return ResponseHelper::success('Super dealer registered successfully.', $user);
    }





    /**
     * @OA\Post(
     *     path="/register/biker",
     *     tags={"Registration"},
     *     summary="Register a Biker",
     *     description="This endpoint registers a new Biker.",
     *     operationId="registerBiker",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "password", "password_confirmation", "sex", "company_id"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="phone", type="string", example="255712345678"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="location", type="string", nullable=true, example="Kisutu, Ilala"),
     *             @OA\Property(property="company_id", type="integer", example=1),
     *             @OA\Property(property="business_name", type="string", nullable=true, example="Mkuya Shop"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
     *             @OA\Property(property="sex", type="string", enum={"male", "female"}, example="male")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Biker registered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Biker registered successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", ref="#/components/schemas/OtherUsers")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object", example={"phone": {"Mobile phone should start with 255"}})
     *         )
     *     )
     * )
     */

    public function biker(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|regex:/^255\d{9}$/|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'sex' => 'nullable|in:male,female',
            // 'super_dealer_id' => 'required|exists:users,id', // Ensure it's a real super dealer
            'vehicle_type' => 'required|string',
            'vehicle_plate_number' => 'required|string|unique:vehicles,plate_number',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }

        try {

            DB::beginTransaction();

            $currentUser = $request->user();
            $currentUserId = $currentUser->id;
            $biker = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'biker',
                'sex' => $request->sex,
                'super_dealer_id' => $currentUserId,
                'company_id' => $currentUser->company_id,
                'created_by' => $currentUserId
            ]);

            // Register vehicle
            $biker->vehicle()->create([
                'type' => $request->vehicle_type,
                'plate_number' => $request->vehicle_plate_number,
            ]);

            DB::commit();

            return ResponseHelper::success('Biker registered successfully.', $biker);

        } catch (Exception $e) {
            return ResponseHelper::error('Failed to register Biker. Error : ' . $e);
        }
    }

}
