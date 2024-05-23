<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

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
				->whereDate("created_at", Carbon::today())
				->sum("grand_total");

			$transactionMonth = $this->user->business->transactions()
				->whereYear("created_at", Carbon::now()->year)
				->whereMonth("created_at", Carbon::now()->month)
				->sum("grand_total");

			return response()->json([
				"today" => $transactionToday,
				"month" => $transactionMonth
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
