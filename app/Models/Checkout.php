<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
	use HasFactory;

	public function transaction() {
		return $this->belongsTo(Transaction::class, "transaction_id");
	}

	public function product() {
		return $this->hasOne(Product::class, "product_id");
	}
}
