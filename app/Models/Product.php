<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Product extends Model
{
	use HasFactory, SoftDeletes;

	protected $casts = [
		'created_at',
		'updated_at'
	];

	protected function serializeDate(DateTimeInterface $date) {
		return $date->format('Y-m-d H:i:s');
	}

	public function business() {
		return $this->belongsTo(Business::class, "business_id");
	}

	public function outlets() {
		return $this->belongsToMany(Outlet::class, "outlet_products", "product_id", "outlet_id")->withTimestamps();
	}
}
