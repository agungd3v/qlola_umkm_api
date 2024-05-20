<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
	use HasFactory;

	public function products() {
		return $this->hasMany(Product::class, "business_id");
	}

	// public function employees() {
	// 	return $this->belongsToMany(User::class, "business_employees", "business_id", "employee_id")->withTimestamps();
	// }
}
