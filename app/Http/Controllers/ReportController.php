<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
	public $user;
	public $outlet_id;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::where("id", Auth::user()->id)->first();
			return $next($request);
		});
	}

	public function getQuickReport(Request $request) {
		try {
			$from = explode(" - ", $request->date)[1];
			$to = explode(" - ", $request->date)[0];

			if ($request->outlet == null) {
				$transaction = Transaction::with("checkouts")
					->where("business_id", $this->user->business->id)
					->whereBetween("created_at", [$from, $to])
					->withCount([
						"checkouts as product_sales" => function($query) {
							$query->select(DB::raw("SUM(quantity) as quantity"))->where("status", "paid");
						}
					])
					->get();
			} else {
				$transaction = $this->user->business->outlets()->where("id", $request->outlet)->first();
				if (!$transaction) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

				$this->outlet_id = $transaction->id;

				$transaction = Transaction::with("checkouts")
					->where("business_id", $this->user->business->id)
					->whereHas("checkouts", function($query) {
						$query->where("outlet_id", $this->outlet_id);
					})
					->whereBetween("created_at", [$from, $to])
					->withCount([
						"checkouts as product_sales" => function($query) {
							$query->select(DB::raw("SUM(quantity) as quantity"))->where("status", "paid");
						}
					])
					->get();
			}

			return response()->json([
				"data" => [
					"sales" => $transaction->sum("grand_total"),
					"count" => $transaction->count(),
					"product_sales" => $transaction->sum("product_sales")
				]
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
