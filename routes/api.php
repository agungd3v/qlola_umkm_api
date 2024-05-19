<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(["middleware" => "verify.request"], function() {
	Route::get("/", function() {
		return response()->json(["message" => "Welcome to Qlola UMKM Mobile Api"], 200);
	});
});