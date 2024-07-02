<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
	public $user;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::with("business")->where("id", Auth::user()->id)->first();
			return $next($request);
		});
	}

	public function getOwnerTransaction() {
		try {
			$userRole = $this->user->role;
			if ($userRole != "owner") throw new \Exception("Tidak ada transaksi");

			$transactionToday = $this->user->business->transactions()
				->whereDate("created_at", Carbon::today());

			$transactionMonth = $this->user->business->transactions()
				->whereYear("created_at", Carbon::now()->year)
				->whereMonth("created_at", Carbon::now()->month);

			return response()->json([
				"transaction_nominal_today" => $transactionToday->sum("grand_total"),
				"transaction_count_today" => $transactionToday->count(),
				"daily_transactions" => $transactionToday->orderBy("id", "desc")->with("checkouts.product", "checkouts.outlet")->get(),
				"transaction_nominal_month" => $transactionMonth->sum("grand_total"),
				"transaction_count_month" => $transactionMonth->count(),
				"monthly_transactions" => $transactionMonth->orderBy("id", "desc")->with("checkouts.product", "checkouts.outlet")->get()
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function getOutletTransaction() {
		try {
			$userRole = $this->user->role;
			if ($userRole != "karyawan") throw new \Exception("Tidak ada transaksi");

			$outlet = $this->user->outlets()->first();
			if (!$outlet) throw new \Exception("Tidak ada transakksi");

			$transaction = Transaction::whereRelation("checkouts", "outlet_id", $outlet->id)
				->where("business_id", $outlet->business->id)
				->whereDate("created_at", Carbon::today());

			return response()->json([
				"transaction_nominal_today" => $transaction->sum("grand_total"),
				"transaction_count_today" => $transaction->count(),
				"transactions" => $transaction->orderBy("id", "desc")->with("checkouts.product", "checkouts.outlet")->get()
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function checkTransaction(Request $request) {
		try {
			$user = auth()->user();
			if ($user->role !== "owner") throw new \Exception("Transaksi tidak ditemukan");

			$user = User::where("id", $user->id)->with("business")->first();
			if (!$user->business) throw new \Exception("Transaksi tidak ditemukan");

			$transaction = Transaction::where("transaction_code", $request->code)
											->where("business_id", $user->business->id)
											->with("checkouts", "others")
											->first();
			if (!$transaction) throw new \Exception("Transaksi tidak ditemukan");

			return response()->json(["data" => $transaction]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function deleteTransaction(Request $request) {
		try {
			DB::beginTransaction();

			$transaction = Transaction::where("id", $request->id)->first();
			if (!$transaction) throw new \Exception("Error, transaction not found!");

			$transaction->delete();

			DB::commit();
			return response()->json(["message" => "Transaksi berhasil di hapus"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function deleteOrderInTransaction(Request $request) {
		try {
			DB::beginTransaction();

			$transaction = Transaction::where("id", $request->transaction_id)->first();
			if (!$transaction) throw new \Exception("Error, transaksi tidak ditemukan!");

			$item = Checkout::where("transaction_id", $transaction->id)->where("product_id", $request->item_id)->first();
			if (!$item) throw new \Exception("Error, item tidak ditemukan!");

			$transaction->grand_total = $transaction->grand_total - $item->total;
			$transaction->save();

			$item->delete();

			if ($transaction->grand_total < 1) {
				$transaction->delete();
			}

			DB::commit();
			return response()->json(["message" => "Item berhasil dihapus!"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
