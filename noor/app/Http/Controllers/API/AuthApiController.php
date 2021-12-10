<?php
namespace App\Http\Controllers\API;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Image;
use Illuminate\Support\Facades\Storage;


class AuthApiController extends Controller
{
    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:6', 'confirmed'],
            'type'       => ['required'],

        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $user = User::create([
            'first_name'     => $request['first_name'],
            'last_name'     => $request['last_name'],
            'email'    => $request['email'],
            'phone'    => $request['phone'],
            'password' => Hash::make($request['password']),
            'type'     => $request->type,
        ]);
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
    public function login(Request $request){
        $credentials = request(['email', 'password']);
        $token = auth()->guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully logged out']);
    }
    protected function respondWithToken($token){
    
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}