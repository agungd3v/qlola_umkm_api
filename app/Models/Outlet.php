<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Outlet extends Model
{
	use HasFactory;

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

	public function products() {
		return $this->belongsToMany(Product::class, "outlet_products", "outlet_id", "product_id")->withTimestamps();
	}

	public function employees() {
		return $this->belongsToMany(User::class, "outlet_employees", "outlet_id", "employee_id")->withTimestamps();
	}
}
