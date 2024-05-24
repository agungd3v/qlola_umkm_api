<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	use HasFactory;

	protected $casts = [
		'created_at' => 'datetime:Y-m-d h:i:s',
		'updated_at' => 'datetime:Y-m-d h:i:s'
	];

	public function checkouts() {
		return $this->hasMany(Checkout::class, "transaction_id");
	}
}
