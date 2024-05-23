<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
  public function signin(Request $request) {
		try {
			if (!$request->phone) throw new \Exception("No. Telepon tidak boleh kosong");
			if (!$request->password) throw new \Exception("Password tidak boleh kosong");

			$credentials = $request->only('phone', 'password');
			$sign = JWTAuth::attempt($credentials);

			if (!$sign) throw new \Exception("Password tidak benar");

			$user = Auth::user();

			if ($user->role == "karyawan") {
				$outlet = User::find(Auth::user()->id)->outlets->first();
				$user["outlet"] = $outlet;
			}
			return response()->json([
				"user" => $user,
				"token" => $sign
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function signup(Request $request) {
		try {
			DB::beginTransaction();

			if (!$request->name) throw new \Exception("Nama tidak boleh kosong");
			if (!$request->phone) throw new \Exception("No. Telepon tidak boleh kosong");
			if (!$request->email) throw new \Exception("Email tidak boleh kosong");
			if (!$request->password) throw new \Exception("Password tidak boleh kosong");
			if (strlen($request->password) < 8) throw new \Exception("Minimal panjang password 8 karakter");
			if (!$request->business_name) throw new \Exception("Nama usaha boleh kosong");

			$user = new User();
			$user->name = $request->name;
			$user->phone = $request->phone;
			$user->email = $request->email;
			$user->email_verified_at = Carbon::now();
			$user->password = Hash::make($request->password);
			$user->save();

			$business = new Business();
			$business->owner_id = $user->id;
			$business->business_name = $request->business_name;
			$business->business_type = $request->business_type;
			$business->save();

			DB::commit();
			return response()->json(["message" => "Successfully register"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function destroy() {
		auth()->invalidate(true);
		return response()->json(['message' => 'Successfully logged out']);
	}

	public function refresh() {
		return response()->json([
			'user' => Auth::user(),
			'token' => JWTAuth::refresh()
		]);
	}
}
