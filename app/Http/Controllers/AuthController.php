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
    public $user;

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $this->user = User::with("business")->where("id", Auth::id())->first();
            return $next($request);
        });
    }

    public function signin(Request $request)
    {
        try {
            $this->validate($request, [
                'phone' => 'required',
                'password' => 'required',
            ]);

            $credentials = $request->only('phone', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new \Exception("Invalid phone number or password");
            }

            // Fetch the authenticated user
            $user = Auth::user();

            if (!$user) {
                throw new \Exception("User not authenticated");
            }

            // Load user with business relationship, but make sure business can be null
            $user = User::with("business")->find($user->id);

            // Check if the user has a business but don't throw an error if null
            if ($user->business === null) {
                $user->business = null;  // Make sure business is explicitly set to null if missing
            }

            // If the user has a 'karyawan' role, fetch outlets and products
            if ($user->role == "karyawan") {
                $outlet = $user->outlets()->with("business", "products")->first();
                $user["outlet"] = $outlet;
            }

            return response()->json([
                "user" => $user,
                "token" => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 400);
        }
    }

    public function signup(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'business_name' => 'required',
            ]);

            DB::beginTransaction();

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
            return response()->json(["message" => "Successfully registered"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message" => $e->getMessage()], 400);
        }
    }

    public function destroy()
    {
        auth()->logout(true);
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'token' => JWTAuth::refresh()
        ]);
    }

    public function check()
    {
        try {
            $user = auth()->user();
            return response()->json(["message" => "Validate user success"]);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(["message" => "Validate user failed"], 401);
        }
    }

    public function addMitra(Request $request)
    {

        $user = User::with("business")->where("id", Auth::id())->first();

        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required',
        ]);

        DB::beginTransaction();

        try {
            // Check if the user already exists with the same business_id and owner_id
            $existingUser = User::where('business_id', $this->user->business->id)
                ->where('owner_id', $this->user->id)
                ->first();

            if ($existingUser) {
                // If the user already exists with the same business_id and owner_id
                throw new \Exception("This user already exists as an owner of the business");
            }

            // Handle photo upload (if exists)
            $fileName = null;
            if ($request->hasFile('photo')) {
                $image = time() . '.' . $request->photo->getClientOriginalExtension();
                $request->file('photo')->move('mitra', $image);
                $fileName = "mitra/" . $image;
            }

            // Create a new mitra (user)
            $user = new User();
            $user->name = $request->name;
            $user->photo = $fileName;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->email_verified_at = Carbon::now();
            $user->password = Hash::make("12345678");  // Default password, can be updated later
            $user->role = "mitra";

            // Ensure the user is associated with the business
            $business = Business::find($this->user->business->id);
            if (!$business) {
                throw new \Exception("Business not found");
            }
            $user->business_id = $business->id;
            $user->owner_id = $this->user->id;  // Assign the owner

            // Save the user
            $user->save();

            // Attach the mitra (user) to the business' employees
            $business->employees()->attach($user->id);

            DB::commit();
            return response()->json([
                "message" => "Successfully registered mitra",
                "user" => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message" => $e->getMessage()], 400);
        }
    }
}
