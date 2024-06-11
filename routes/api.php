<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;


Route::group(["middleware" => ["verify.request"]], function() {
	Route::get("/", function() {
		return response()->json(["message" => "Welcome to Qlola UMKM Mobile Api"], 200);
	});

	Route::get("/test", function() {
		return auth()->user()->id;
	})->middleware("jwt.verify");

	Route::post("/signin", [AuthController::class, "signin"]);
	Route::post("/signup", [AuthController::class, "signup"]);
	Route::post("/refresh", [AuthController::class, "refresh"]);
	Route::post("/logout", [AuthController::class, "destroy"]);
	Route::get("/check", [AuthController::class, "check"])->middleware("jwt.verify");


	Route::group(["prefix" => "outlet", "middleware" => ["jwt.verify"]], function() {
		Route::get("/", [OutletController::class, "listOutlet"]);
		Route::post("/", [OutletController::class, "addOutlet"]);
		Route::get("/employees", [OutletController::class, "getAvailEmployee"]);
		Route::get("/employees/{outlet_id}", [OutletController::class, "getOutleEmployee"]);
		Route::post("add-employee", [OutletController::class, "addEmployee"]);
		Route::delete("remove-employee", [OutletController::class, "removeEmployee"]);
		Route::post("/products", [OutletController::class, "getAvailProduct"]);
		Route::get("/products/{outlet_id}", [OutletController::class, "getOutletProduct"]);
		Route::post("add-product", [OutletController::class, "addProduct"]);
		Route::delete("remove-product", [OutletController::class, "removeProduct"]);
	});

	Route::group(["prefix" => "product", "middleware" => ["jwt.verify"]], function() {
		Route::get("/", [ProductController::class, "listProduct"]);
		Route::post("/", [ProductController::class, "addProduct"]);
	});

	Route::group(["prefix" => "employee", "middleware" => ["jwt.verify"]], function() {
		Route::get("/", [EmployeeController::class, "listEmployee"]);
		Route::post("/", [EmployeeController::class, "addEmployee"]);
		Route::get("/product", [EmployeeController::class, "getProduct"]);
	});

	Route::group(["prefix" => "order", "middleware" => ["jwt.verify"]], function() {
		Route::post("/transaction", [CheckoutController::class, "transaction"]);
		Route::post("/transaction/bulk", [CheckoutController::class, "transactionBulk"]);
	});

	Route::group(["prefix" => "transaction", "middleware" => ["jwt.verify"]], function() {
		Route::get("/owner", [TransactionController::class, "getOwnerTransaction"]);
		Route::get("/outlet", [TransactionController::class, "getOutletTransaction"]);
	});

	Route::group(["prefix" => "report", "middleware" => ["jwt.verify"]], function() {
		Route::post("quick", [ReportController::class, "getQuickReport"]);
	});
});