<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;

class JwtMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, Closure $next)
	{
		try {
			JWTAuth::parseToken()->authenticate();
		} catch (\Exception $e) {
			if ($e instanceof TokenInvalidException){
				return response()->json(['message' => 'Authorization Invalid'], 401);
			} else if ($e instanceof TokenExpiredException){
				// return response()->json(['status' => 'Authorization Expired'], 401);
				return response()->json(['message' => 'Authorization Invalid'], 401);
			} else{
				// return response()->json(['status' => 'Authorization token not found'], 401);
				return response()->json(['message' => 'Authorization Invalid'], 401);
			}
		}
		return $next($request);
	}
}
