<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Business extends Model
{
	use HasFactory;

	protected $casts = [
		'created_at',
		'updated_at'
	];

	protected function serializeDate(DateTimeInterface $date) {
		return $date->format('Y-m-d H:i:s');
	}

	public function products() {
		return $this->hasMany(Product::class, "business_id");
	}

	public function employees() {
		return $this->belongsToMany(User::class, "business_employees", "business_id", "employee_id")->withTimestamps();
	}

	public function outlets() {
		return $this->hasMany(Outlet::class, "business_id");
	}

	public function transactions() {
		return $this->hasMany(Transaction::class, "business_id");
	}
}
