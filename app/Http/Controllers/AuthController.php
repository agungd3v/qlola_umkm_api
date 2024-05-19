<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  public function signin(Request $request) {
		try {
			if (!$request->phone) throw new \Exception("No. Telepon tidak boleh kosong");
			if (!$request->password) throw new \Exception("Password tidak boleh kosong");

			$credentials = $request->only('phone', 'password');
			$token = Auth::attempt($credentials);

			if (!$token) throw new \Exception("Password tidak benar");

			$user = Auth::user();
			return response()->json([
				"user" => $user,
				"token" => $token
			]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function signup(Request $request) {
		try {
			if (!$request->name) throw new \Exception("Nama tidak boleh kosong");
			if (!$request->phone) throw new \Exception("No. Telepon tidak boleh kosong");
			if (!$request->email) throw new \Exception("Email tidak boleh kosong");
			if (!$request->password) throw new \Exception("Password tidak boleh kosong");
			if (count($request->password) < 8) throw new \Exception("Minimal panjang password 8 karakter");

			$user = new User();
			$user->name = $request->name;
			$user->phone = $request->phone;
			$user->email = $request->email;
			$user->password = Hash::make($request->password);
			$user->save();

			return response()->json(["message" => "Successfully register"]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function signout() {
		Auth::logout();
		return response()->json(['message' => 'Successfully logged out']);
	}

	public function refresh() {
		return response()->json([
			'user' => Auth::user(),
			'token' => Auth::refresh()
		]);
	}
}
