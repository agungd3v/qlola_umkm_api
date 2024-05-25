<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OutletController extends Controller
{
	public $user;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::where("id", Auth::user()->id)->first();
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

	public function getOutletProduct() {
		try {
			$product = Product::orderBy("id", "desc")->get();
			return response()->json(["data" => $product]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function getOutleEmployee(Request $request, $outlet_id) {
		try {
			$outlet = $this->user->business->outlets()->where("id", $outlet_id)->first();
			if (!$outlet) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

			$outlet = $outlet->employees;
			return response()->json(["data" => $outlet]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function addEmployee(Request $request) {
		try {
			DB::beginTransaction();

			$outlet = $this->user->business->outlets()->where("id", $request->outlet_id)->first();
			if (!$outlet) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

			$employee = $this->user->business->employees()->where("employee_id", $request->employee_id)->first();
			if (!$employee) throw new \Exception("Karyawan diluar jangkauan bisnis kamu");

			$check = DB::table("outlet_employees")
				->where("outlet_id", $outlet->id)
				->where("employee_id", $employee->id)
				->first();

			if ($check) throw new \Exception("Karyawan sudah berada di outlet ini");

			DB::table("outlet_employees")->insert([
				"outlet_id" => $outlet->id,
				"employee_id" => $employee->id,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now()
			]);

			DB::commit();
			return response()->json(["message" => "Success"]);
		} catch (\Throwable $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
	
	public function removeEmployee(Request $request) {
		try {
			DB::beginTransaction();

			$outlet = $this->user->business->outlets()->where("id", $request->outlet_id)->first();
			if (!$outlet) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

			$outlet = $outlet->employees()->where("employee_id", $request->employee_id)->first();
			if (!$outlet) throw new \Exception("Karyawan tidak berada di outlet ini");

			DB::table("outlet_employees")
				->where("outlet_id", $outlet->pivot->outlet_id)
				->where("employee_id", $outlet->pivot->employee_id)
				->delete();

			DB::commit();
			return response()->json(["message" => "Success"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function addProduct(Request $request) {
		try {
			DB::beginTransaction();

			$outlet = $this->user->business->outlets()->where("id", $request->outlet_id)->first();
			if (!$outlet) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

			$product = $this->user->business->products()->where("id", $request->product_id)->first();
			if (!$product) throw new \Exception("Produk diluar jangkauan bisnis kamu");

			$check = DB::table("outlet_products")
				->where("outlet_id", $outlet->id)
				->where("product_id", $product->id)
				->first();

			if ($check) throw new \Exception("Produk sudah tersedia di outlet ini");

			DB::table("outlet_products")->insert([
				"outlet_id" => $outlet->id,
				"product_id" => $product->id,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now()
			]);

			DB::commit();
			return response()->json(["message" => "Success"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function removeProduct(Request $request) {
		try {
			DB::beginTransaction();

			$outlet = $this->user->business->outlets()->where("id", $request->outlet_id)->first();
			if (!$outlet) throw new \Exception("Outlet diluar jangkauan bisnis kamu");
			
			$outlet = $outlet->products()->where("product_id", $request->product_id)->first();
			if (!$outlet) throw new \Exception("Product tidak berada di outlet ini");

			DB::table("outlet_products")
				->where("outlet_id", $outlet->pivot->outlet_id)
				->where("product_id", $outlet->pivot->product_id)
				->delete();

			DB::commit();
			return response()->json(["message" => "Success"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
