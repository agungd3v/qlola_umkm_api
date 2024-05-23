<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
	use HasFactory;

	public function products() {
		return $this->belongsToMany(Product::class, "outlet_products", "outlet_id", "product_id")->withTimestamps();
	}

	public function business() {
		return $this->belongsTo(Business::class, "business_id");
	}

	public function employees() {
		return $this->belongsToMany(User::class, "outlet_employees", "outlet_id", "employee_id")->withTimestamps();
	}
}
