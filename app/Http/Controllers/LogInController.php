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
     *Log in function but only for biker
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
