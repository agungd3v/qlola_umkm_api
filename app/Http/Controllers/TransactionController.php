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

			// $transactionMonth = $this->user->business->transactions()
			// 	->whereYear("created_at", Carbon::now()->year)
			// 	->whereMonth("created_at", Carbon::now()->month);

			$transactionsMonth = $this->user->business->transactions()
				->whereYear("created_at", Carbon::now()->year)
				->whereMonth("created_at", Carbon::now()->month)
				->with("checkouts.product", "checkouts.outlet", "others")
				->orderBy("id", "desc")
				->get();

			return response()->json([
				"transaction_nominal_today" => $transactionToday->sum("grand_total"),
				"transaction_count_today" => $transactionToday->count(),
				"daily_transactions" => $transactionToday
                ->orderBy("id", "desc")
                ->with("checkouts.product", "checkouts.outlet", "others")
                ->get(),				
				"transaction_nominal_month" => $transactionsMonth->sum("grand_total"),
				"transaction_count_month" => $transactionsMonth->count(),
				"monthly_transactions" => $transactionsMonth
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function getOwnerTransactionSummary() {
		try {
			$userRole = $this->user->role;
			if ($userRole != "owner") throw new \Exception("Tidak ada transaksi");

			$trxPendingToday = $this->user->business->transactions()
				->whereDate("created_at", Carbon::today())
				->where("status", "=", "pending")
				->sum("grand_total");

			$trxSuccessToday = $this->user->business->transactions()
				->whereDate("created_at", Carbon::today())
				->where("status", "=", "success")
				->sum("grand_total");

			$trxVoidToday = $this->user->business->transactions()
				->whereDate("created_at", Carbon::today())
				->where("status", "=", "void")
				->sum("grand_total");

			$transactionsMonth = $this->user->business->transactions()
				->whereYear("created_at", Carbon::now()->year)
				->whereMonth("created_at", Carbon::now()->month)
				->where("status", "=", "success")
				->sum("grand_total");

			return response()->json([
				"status" => 200,
				"transaction_pending_today" => floatval($trxPendingToday),
				"transaction_success_today" => floatval($trxSuccessToday),
				"transaction_void_today" => floatval($trxVoidToday),
				"transaction_success_month" => floatval($transactionsMonth)
			]);
		} catch (\Exception $e) {
			return response()->json([
				"status" => 400,
				"message" => $e->getMessage()
			], 400);
		}
	}

	public function getOutletTransactionSummary(Request $request, $type)
	{
		try {
			$userRole = $this->user->role;
			if ($userRole != "karyawan") throw new \Exception("Tidak ada transaksi");

			$outlet = $this->user->outlets()->first();
			if (!$outlet) throw new \Exception("Tidak ada transakksi");
			
			$transaction = Transaction::whereRelation("checkouts", "outlet_id", $outlet->id)
				->where("business_id", $outlet->business->id)
				->whereDate("created_at", Carbon::today());

			return response()->json([
				"status" => 200,
				"data" => $transaction->where("status", "=", $type)->sum("grand_total")
			]);
		} catch (\Exception $e) {
			return response()->json([
				"status" => 400,
				"message" => $e->getMessage()
			], 400);
		}
	}

	public function getOutletTransactionPart(Request $request, $type)
	{
		try {
			$userRole = $this->user->role;
			if ($userRole != "karyawan") throw new \Exception("Tidak ada transaksi");

			$outlet = $this->user->outlets()->first();
			if (!$outlet) throw new \Exception("Tidak ada transakksi");
			
			$transaction = Transaction::whereRelation("checkouts", "outlet_id", $outlet->id)
				->where("business_id", $outlet->business->id)
				->whereDate("created_at", Carbon::today())
				->with("checkouts.product", "checkouts.outlet", "others");

			return response()->json([
				"status" => 200,
				"data" => $transaction->where("status", "=", $type)->orderBy("id", "asc")->get()
			]);
		} catch (\Exception $e) {
			return response()->json([
				"status" => 400,
				"message" => $e->getMessage()
			], 400);
		}
	}

	public function getOutletTransaction(Request $request)
	{
		try {
			$userRole = $this->user->role;
			if ($userRole != "karyawan") throw new \Exception("Tidak ada transaksi");

			$outlet = $this->user->outlets()->first();
			if (!$outlet) throw new \Exception("Tidak ada transakksi");
			
			$transaction = Transaction::whereRelation("checkouts", "outlet_id", $outlet->id)
				->where("business_id", $outlet->business->id)
				->whereDate("created_at", Carbon::today())
				->with("checkouts.product", "checkouts.outlet", "others");

			return response()->json([
				"status" => 200,
				"transactions" => $transaction->orderBy("id", "desc")->get()
			]);
		} catch (\Exception $e) {
			return response()->json([
				"status" => 400,
				"message" => $e->getMessage()
			], 400);
		}
	}

	public function checkTransaction(Request $request)
	{
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

	public function deleteTransaction(Request $request)
	{
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

	public function deleteOrderInTransaction(Request $request)
	{
		try {
			DB::beginTransaction();

			$transaction = Transaction::where("id", $request->transaction_id)->first();
			if (!$transaction) throw new \Exception("Error, transaksi tidak ditemukan!");

			$item = Checkout::where("transaction_id", $transaction->id)->where("id", $request->item_id)->first();
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

	public function getDailyTransactionEmployee()
	{
		$user = Auth::user();
		// If the user has a 'karyawan' role, fetch outlets and products
		if ($user->role == "karyawan") {
			$outlet = User::find(Auth::user()->id)->outlets()->with("business", "products")->first();
			$user["outlet"] = $outlet;
		}

		return response()->json([
			"user" => $user,
		]);
	}

	public function cancelTransaction(Request $request) {
		try {
			DB::beginTransaction();

			$transaction = Transaction::where("id", $request->transaction_id)->first();
			if (!$transaction) throw new \Exception("Error, transaction tidak ditemukan");

			$transaction->status = "void";
			$transaction->reason_void = $request->reason ?? NULL;
			$transaction->void_from = Auth::user()->role;
			$transaction->save();

			DB::commit();

			return response()->json([
				"status" => 200,
				"message"=> "Success"
			]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				"status" => 400,
				"message"=> $e->getMessage()
			], 400);
		}
	}

	public function confirmTransaction(Request $request) {
		try {
			DB::beginTransaction();

			$transaction = Transaction::where("id", $request->transaction_id)->first();
			if (!$transaction) throw new \Exception("Error, transaction tidak ditemukan");

			$transaction->status = "success";
			$transaction->save();

			DB::commit();

			return response()->json([
				"status" => 200,
				"message"=> "Success"
			]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				"status" => 400,
				"message"=> $e->getMessage()
			], 400);
		}
	}
}
