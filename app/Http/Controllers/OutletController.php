<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OutletController extends Controller
{
	public $user;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::with("business")->where("id", Auth::user()->id)->first();
			return $next($request);
		});
	}

	public function listOutlet() {
		try {
			$outlet = Outlet::where("business_id", $this->user->business->id)->orderBy("id", "desc")->get();
			return response()->json(["data" => $outlet]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

  public function addOutlet(Request $request) {
		try {
			DB::beginTransaction();

			$outlet = new Outlet();
			$outlet->business_id = $this->user->business->id;
			$outlet->outlet_name = $request->outlet_name;
			$outlet->outlet_phone = $request->outlet_phone;
			$outlet->outlet_address = $request->outlet_address;
			$outlet->save();

			DB::commit();
			return response()->json(["message" => "Outlet created"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
