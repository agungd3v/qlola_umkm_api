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
	public $outlet_id;

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

	public function getOutletProduct(Request $request, $outlet_id) {
		try {
			$outlet = $this->user->business->outlets()->where("id", $outlet_id)->first();
			if (!$outlet) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

			$outlet = $outlet->products;
			return response()->json(["data" => $outlet]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function getAvailEmployee() {
		try {
			$employee = $this->user->business->employees()->doesnthave("outlets")->get();

			return response()->json(["data" => $employee]);
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

			// $requestEmployees = count($request->employees);
			// if ($requestEmployees < 1) throw new \Exception("Minimal harus memiliki 1 karyawan terdaftar");

			$employees = [];
			for ($i = 0; $i < count($request->employees); $i++) { 
				$employee = $this->user->business->employees()->where("employee_id", $request->employees[$i]["id"])->first();
				if (!$employee) throw new \Exception("Karyawan diluar jangkauan bisnis kamu");

				$employees[] = $employee->id;
			}

			$outlet->employees()->sync($employees);

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

			// $requestProducts = count($request->products);
			// if ($requestProducts < 1) throw new \Exception("Minimal harus memiliki 1 product terdaftar");

			$products = [];
			for ($i = 0; $i < count($request->products); $i++) { 
				$product = $this->user->business->products()->where("id", $request->products[$i]["id"])->first();
				if (!$product) throw new \Exception("Produk diluar jangkauan bisnis kamu");

				$products[] = $product->id;
			}

			$outlet->products()->sync($products);

			DB::commit();
			return response()->json(["message" => "Success"]);
		} catch (\Throwable $e) {
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

	public function getAvailProduct(Request $request) {
		try {
			$this->outlet_id = $request->outlet_id;

			$product = $this->user->business->products()->whereDoesntHave("outlets", function($query) {
				$query->where("outlet_id", $this->outlet_id);
			})->get();

			return response()->json(["data" => $product]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
