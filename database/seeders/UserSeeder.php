<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// Owner
		$user = new User();
		$user->name = "Agung Ardiyanto";
		$user->phone = "+6289668951090";
		$user->email = "owner@email.com";
		$user->email_verified_at = Carbon::now();
		$user->password = Hash::make("12345678");
		$user->save();

		// Karyawan
		$user = new User();
		$user->name = "Agung Ardiyanto";
		$user->phone = "+6281283134033";
		$user->email = "karyawan@email.com";
		$user->email_verified_at = Carbon::now();
		$user->password = Hash::make("12345678");
		$user->role = "karyawan";
		$user->save();

		// Mitra
		$user = new User();
		$user->name = "Agung Ardiyanto";
		$user->phone = "+6281945156563";
		$user->email = "mitra@email.com";
		$user->email_verified_at = Carbon::now();
		$user->password = Hash::make("12345678");
		$user->role = "mitra";
		$user->save();

		$business = new Business();
		$business->owner_id = 1;
		$business->business_name = "Bisnis";
		$business->save();

		DB::table("business_employees")->insert([
			"business_id" => 1,
			"employee_id" => 2
		]);
	}
}
