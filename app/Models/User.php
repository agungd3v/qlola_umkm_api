<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
	use HasApiTokens, HasFactory, Notifiable;

	protected $fillable = [
		'name',
		'photo',
		'phone',
		'email',
		'password',
		'role'
	];

	protected $hidden = [
		'password',
		'remember_token',
		'created_at',
		'updated_at'
	];

	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public function business() {
		return $this->hasOne(Business::class, "owner_id");
	}

	public function outlets() {
		return $this->belongsToMany(Outlet::class, "outlet_employees", "employee_id", "outlet_id")->withTimestamps();
	}

	public function getJWTIdentifier() {
		return $this->getKey();
	}

	public function getJWTCustomClaims() {
		return [];
	}
}
