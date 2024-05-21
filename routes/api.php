<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(["middleware" => ["verify.request"]], function() {
	Route::get("/", function() {
		return response()->json(["message" => "Welcome to Qlola UMKM Mobile Api"], 200);
	});

	Route::post("/signin", [AuthController::class, "signin"]);
	Route::post("/signup", [AuthController::class, "signup"]);
	Route::post("/refresh", [AuthController::class, "refresh"]);
	Route::post("/logout", [AuthController::class, "destroy"]);

	Route::group(["prefix" => "outlet", "middleware" => ["jwt.verify"]], function() {
		Route::get("/", [OutletController::class, "listOutlet"]);
		Route::post("/", [OutletController::class, "addOutlet"]);
		Route::get("/product", [OutletController::class, "getOutletProduct"]);
	});

	Route::group(["prefix" => "product", "middleware" => ["jwt.verify"]], function() {
		Route::get("/", [ProductController::class, "listProduct"]);
		Route::post("/", [ProductController::class, "addProduct"]);
	});

	Route::group(["prefix" => "employee", "middleware" => ["jwt.verify"]], function() {
		Route::get("/", [EmployeeController::class, "listEmployee"]);
		Route::post("/", [EmployeeController::class, "addEmployee"]);
	});
});