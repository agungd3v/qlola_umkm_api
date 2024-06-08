<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
  public $user;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::where("id", Auth::user()->id)->first();
			return $next($request);
		});
	}

	public function transaction(Request $request) {
		try {
			DB::beginTransaction();

			if (!$request->total || !$request->outlet_id || !$request->business_id || !$request->products || count($request->products) < 1) {
				throw new \Exception("Kesalahan, gagal memproses pesanan");
			}

			$checkOutlet = $this->user->outlets()->first();
			if (!$checkOutlet) throw new \Exception("Kesalahan, gagal memproses pesanan");
			if ($checkOutlet->business_id != $request->business_id) throw new \Exception("Kesalahan, gagal memproses pesanan");
			if ($checkOutlet->id != $request->outlet_id) throw new \Exception("Kesalahan, gagal memproses pesanan");

			$transaction = new Transaction();
			$transaction->transaction_code = "TGA-". time() . rand(10, 99);
			$transaction->business_id = $request->business_id;
			$transaction->grand_total = $request->total;
			$transaction->save();

			for ($index = 0; $index < count($request->products); $index++) { 
				$checkout = new Checkout();
				$checkout->transaction_id = $transaction->id;
				$checkout->outlet_id = $checkOutlet->id;
				$checkout->product_id = $request->products[$index]["id"];
				$checkout->quantity = $request->products[$index]["quantity"];
				$checkout->total = floatval($request->products[$index]["product_price"]) * $request->products[$index]["quantity"];
				$checkout->status = "paid";
				$checkout->save();
			}

			DB::commit();
			return response()->json(["message" => $transaction->transaction_code]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function transactionBulk(Request $request) {
		try {
			DB::beginTransaction();

			$total = 0;

			foreach ($request->data as $key => $data) {
				$transaction = new Transaction();
				$transaction->transaction_code = "TGA-". time() . rand(10, 99);
				$transaction->business_id = $request->business_id;
				$transaction->grand_total = 0;
				$transaction->save();

				foreach ($data as $key2 => $item) {
					$checkout = new Checkout();
					$checkout->transaction_id = $transaction->id;
					$checkout->outlet_id = $item["_outletid"];
					$checkout->product_id = $item["_productid"];
					$checkout->quantity = $item["_quantity"];
					$checkout->total = $item["_total"];
					$checkout->status = "paid";
					$checkout->created_at = $item["_createdat"];
					$checkout->updated_at = $item["_updatedat"];
					$checkout->save();

					$total += $checkout->total;
				}

				$transaction->grand_total = $total;
				$transaction->save();

				$total = 0;
			}
			
			DB::commit();
			return response()->json(["message" => "Berhasil menyinkronkan data dengan server"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
