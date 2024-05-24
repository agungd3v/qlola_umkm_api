<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Transaction extends Model
{
	use HasFactory;

	protected $casts = [
		'created_at',
		'updated_at'
	];

	protected function serializeDate(DateTimeInterface $date) {
		return $date->format('Y-m-d H:i:s');
	}

	public function checkouts() {
		return $this->hasMany(Checkout::class, "transaction_id");
	}
}
