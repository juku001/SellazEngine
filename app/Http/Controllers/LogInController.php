<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Validator;

class LogInController extends Controller
{
    /**
     * Log In function but for every one else.
     * 
     * 
     * 
     * @OA\Post(
     *     path="/login",
     *     summary="Login User",
     *     description="Authenticates users and returns a JWT token. This api can be used by anyone, but specifically Super admins.",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged in successful"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="1|abc123tokenvalue"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="user_type", type="string", example="admin"),
     *                     @OA\Property(property="department", type="string", example="Finance")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials or validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid account credentials."),
     *             @OA\Property(property="code", type="integer", example=401),
     *             
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Account does not exist."),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }
        $user = User::with('company')->where('email', $request->email)->first();

        if (!$user) {
            return ResponseHelper::error('Account does not exist.', [], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid account credentials.', [], 401);
        }
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['user'] = $user;
        return ResponseHelper::success('User login successfully.', $success);
    }





    /**
     *Log in function but only for biker and super dealers
     */


    /**
     * @OA\Post(
     *     path="/login/app",
     *     summary="App login for bikers and super dealers",
     *     description="Logs in a user via phone and password. Only bikers and super dealers are allowed to log in.",
     *     operationId="appLogin",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="255712345678", description="Phone number starting with 255"),
     *             @OA\Property(property="password", type="string", example="secret123", description="User's password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example=true),
     *             @OA\Property(property="message", type="string", example="User login successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|f3fa...token...string"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="role", type="string", example="biker"),
     *                     @OA\Property(property="phone", type="string", example="255712345678"),
     *                     @OA\Property(property="company", type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="EasyTrack Ltd."),
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid account credentials."),
     *             @OA\Property(property="code", type="integer", example=401),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied (non-biker/superdealer)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example=false),
     *             @OA\Property(property="message", type="string", example="Access denied. Only bikers and superdealers can log in here."),
     *             @OA\Property(property="code", type="integer", example=403),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Account does not exist."),
     *             @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422")
     * )
     */

    public function app(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^255\d{9}$/',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }
        $user = User::with('company')->where('phone', $request->phone)->first();

        if (!$user) {
            return ResponseHelper::error('Account does not exist.', [], 404);

        }
        if (!Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid account credentials.', [], 401);
        }

        if ($user->role === 'super_admin') {
            return ResponseHelper::error('Access denied. Only bikers and superdealers can log in here.', [], 403);
        }
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['user'] = $user;
        return ResponseHelper::success('User login successfully.', $success);
    }




    /**
     * Log out the currently authenticated user (biker) by deleting their current token
     */
    /**
     * @OA\Post(
     *   tags={"Authentication"},
     *   path="/logout",
     *   summary="This API logs the user out from the system.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200, 
     *     description="OK",
     *     @OA\JsonContent(
     *         @OA\Property(property="status", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Successfully logged out."),
     *         @OA\Property(property="code", type="integer", example=200),
     *     ) 
     *   ),
     *   @OA\Response(response=401, ref="#/components/responses/401"),
     *   
     *   @OA\Response(
     *     response=500, 
     *     description="User not logged in",
     *      @OA\JsonContent(
     *         @OA\Property(property="status", type="boolean", example=false),
     *         @OA\Property(property="message", type="string", example="User not logged in."),
     *         @OA\Property(property="code", type="integer", example=500),
     *     )
     *   )
     * )
     */

    public function destroy(Request $request)
    {
        // Revoke the token used in the current request
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return ResponseHelper::success('Successfully logged out.', []);
        } else {
            return ResponseHelper::error('User not logged in.', []);
        }
    }

}
