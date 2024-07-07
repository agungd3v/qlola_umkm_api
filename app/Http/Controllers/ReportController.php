<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
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
			$from = explode(" - ", $request->date)[0];
			$to = explode(" - ", $request->date)[1];

			if (isset($request->is_custom)) {
				if ($request->is_custom) {
					$to = Carbon::parse($to)->addDay(1)->format("Y-m-d");
				}
			}

			if ($request->outlet == null) {
				$transaction = Transaction::with("checkouts", "checkouts.product", "others")
					->where("business_id", $this->user->business->id)
					->whereBetween("created_at", [$from, $to])
					->withCount([
						"checkouts as product_sales" => function($query) {
							$query->select(DB::raw("SUM(quantity) as quantity"))->where("status", "paid");
						}
					])
					->withCount([
						"others as product_other_sales" => function($query) {
							$query->select(DB::raw("SUM(quantity) as quantity"))->where("status", "paid");
						}
					])
					->get();
			} else {
				$transaction = $this->user->business->outlets()->where("id", $request->outlet)->first();
				if (!$transaction) throw new \Exception("Outlet diluar jangkauan bisnis kamu");

				$this->outlet_id = $transaction->id;

				$transaction = Transaction::with("checkouts", "checkouts.product", "others")
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
					->withCount([
						"others as product_other_sales" => function($query) {
							$query->select(DB::raw("SUM(quantity) as quantity"))->where("status", "paid");
						}
					])
					->get();
			}

			return response()->json([
				"data" => [
					"sales" => $transaction->sum("grand_total"),
					"count" => $transaction->count(),
					"product_sales" => $transaction->sum("product_sales"),
					"product_other_sales" => $transaction->sum("product_other_sales"),
					"transactions" => $transaction
				]
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
