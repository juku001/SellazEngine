<?php
namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     * Accepts a list of allowed user types like: ->middleware('check.user_type:superadmin,admin')
     */
    public function handle(Request $request, Closure $next, ...$allowedTypes): Response
    {
        $user = $request->user(); // works with Sanctum
        if (!$user) {
            return ResponseHelper::error(
                'Unauthorized',
                [],
                401
            );
        }
        if (!in_array($user->role, $allowedTypes)) {
            return ResponseHelper::error(
                'Unauthorized User Type',
                [],
                403
            );
        }
        return $next($request);
    }
}
