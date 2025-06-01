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
        $user = User::where('email', $request->email)->first();

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
     *Log in function but only for biker
     */
    public function biker(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^255\d{9}$/',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation Error.', $validator->errors(), 422);
        }
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return ResponseHelper::error('Account does not exist.', [], 404);

        }
        if (!Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid account credentials.', [], 401);
        }

        if ($user->user_type !== 'biker') {
            return ResponseHelper::error('Access denied. Only bikers can log in here.', [], 403);
        }
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['name'] = $user->name;
        $success['profile_pic'] = $user->profile_pic;
        $success['type'] = $user->user_type;
        return ResponseHelper::success('User login successfully.', $success);
    }




    /**
     * Log out the currently authenticated user (biker) by deleting their current token
     */
    public function destroy(Request $request)
    {
        // Revoke the token used in the current request
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return ResponseHelper::success('Successfully logged out.', []);
        }else{
            return ResponseHelper::error('User not logged in.', []);
        }
    }

}
