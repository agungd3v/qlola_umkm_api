<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
	public $user;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::with("business")->where("id", Auth::user()->id)->first();
			return $next($request);
		});
	}

	public function listProduct() {
		try {
			$product = Product::where("business_id", $this->user->business->id)->orderBy("id", "desc")->get();
			return response()->json(["data" => $product]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function addProduct(Request $request) {
		$fileName = "";

		try {
			DB::beginTransaction();

			if (!$request->product_name) throw new \Exception("Nama produk tidak boleh kosong");
			if (!$request->product_price) throw new \Exception("Harga produk tidak boleh kosong");
			if (!$request->product_image) throw new \Exception("Foto produk tidak boleh kosong");
			if (!$request->hasFile("product_image")) throw new \Exception("Foto produk tidak valid");

			$image = time() . '.' . $request->product_image->getClientOriginalExtension();
			$request->file("product_image")->move('products', $image);

			$fileName = "products/" . $image;

			$product = new Product();
			$product->business_id = $this->user->business->id;
			$product->product_image = $fileName;
			$product->product_name = $request->product_name;
			$product->product_price = $request->product_price;
			$product->product_favorite = $request->product_favorite;
			$product->save();

			DB::commit();
			return response()->json(["message" => "Product created"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
