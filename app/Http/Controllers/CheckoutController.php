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
			return response()->json(["message" => "Transaction successfully"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
