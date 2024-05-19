<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(["middleware" => "verify.request"], function() {
	Route::get("/", function() {
		return response()->json(["message" => "Welcome to Qlola UMKM Mobile Api"], 200);
	});

	Route::post("/signin", [AuthController::class, "signin"]);
	Route::post("/signup", [AuthController::class, "signup"]);
	Route::post("/refresh", [AuthController::class, "refresh"]);
	Route::post("/logout", [AuthController::class, "logout"]);
});