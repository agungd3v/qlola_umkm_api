<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Checkout extends Model
{
	use HasFactory;

	protected $casts = [
		'created_at',
		'updated_at'
	];

	protected function serializeDate(DateTimeInterface $date) {
		return $date->format('Y-m-d H:i:s');
	}

	public function transaction() {
		return $this->belongsTo(Transaction::class, "transaction_id");
	}

	public function product() {
		return $this->belongsTo(Product::class, "product_id");
	}

	public function outlet() {
		return $this->belongsTo(Outlet::class, "outlet_id");
	}
}
