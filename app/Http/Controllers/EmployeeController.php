<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
	public $user;

	public function __construct(Request $request) {
		$this->middleware(function($request, $next) {
			$this->user = User::where("id", Auth::user()->id)->first();
			return $next($request);
		});
	}

	public function listEmployee() {
		try {
			$employee = $this->user->business->employees()->orderBy("id", "desc")->get();
			return response()->json(["data" => $employee]);
		} catch (\Exception $e) {
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}

	public function addEmployee(Request $request) {
		$fileName = "";

		try {
			DB::beginTransaction();

			if (!$request->name) throw new \Exception("Nama tidak boleh kosong");
			if (!$request->phone) throw new \Exception("No. Telepon tidak boleh kosong");
			if (!$request->photo) throw new \Exception("Photo tidak boleh kosong");
			if (!$request->hasFile("photo")) throw new \Exception("Foto karyawan tidak valid");

			$image = time() . '.' . $request->photo->getClientOriginalExtension();
			$request->file("photo")->move('employees', $image);

			$fileName = "employees/" . $image;

			
			$user = new User();
			$user->name = $request->name;
			$user->photo = $fileName;
			$user->phone = $request->phone;
			$user->email = time() . "@indonesia.id";
			$user->email_verified_at = Carbon::now();
			$user->password = Hash::make("12345678");
			$user->role = "karyawan";
			$user->save();

			$this->user->business->employees()->attach($user->id);

			DB::commit();
			return response()->json(["message" => "Successfully register"]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json(["message" => $e->getMessage()], 400);
		}
	}
}
