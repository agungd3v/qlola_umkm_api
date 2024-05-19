<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyRequestMiddleware
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
		$qlolaApp = $request->header('X-REQUEST-QLOLA-UMKM-MOBILE');
		if (!$qlolaApp || $qlolaApp != env("MOBILE_APP_KEY")) {
			return response()->json(["message" => "Unauthorized"], 401);
		}
		return $next($request);
	}
}
