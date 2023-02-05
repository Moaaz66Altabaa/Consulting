<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class isExpertMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->isExpert){
            return response()->json([
                'status' => 0,
                'message' => 'you are not authorized'
            ] , 403);
        }
        return $next($request);
    }
}
