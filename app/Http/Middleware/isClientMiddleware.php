<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class isClientMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() instanceof User){
            return response()->json([
                'status' => 0,
                'message' => 'you are not authorized'
            ] , 403);
        }
        return $next($request);
    }
}
