<?php

namespace App\Http\Middleware;

use App\Models\Expert;
use Closure;
use Illuminate\Http\Request;

class isExpertMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() instanceof Expert){
            return response()->json([
                'status' => 0,
                'message' => 'you are not authorized'
            ] , 403);
        }
        return $next($request);
    }
}
