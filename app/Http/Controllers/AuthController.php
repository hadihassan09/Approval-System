<?php

namespace App\Http\Controllers;

use App\Models\Temp;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Logs the user in by creating his specific token.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $user = User::where('email', $request->input('email'))->firstOrFail();

        if(Hash::check($request->input('password'), $user->password)){

            $token = Token::create([
                'token'=>Str::random(60),
                'user_id'=> $user->id
            ]);

            return response()->json([
                'access_token' => $token,
                'email' => $user->email
            ]);

        }
        return response()->json(['status' => 'Incorrect Credentials'],401);
    }

    /**
     * Registers a new user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'address' => 'required|string',
            'role' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $params = [
            0=>['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)],
            1=>['address' => $request->address, 'user_id' => '#'],
            2=>['role' => $request->role, 'user_id' => '#'],
        ];

        $user = new \stdClass();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->info = ['address' => $request->address];
        $user->role = ['role' => $request->role];

        $temp = Temp::create([
            'type'=>'create',
            'table' => json_encode(['users', 'users_information', 'users_roles']),
            'bindings' => json_encode($params),
            'output' => json_encode($user)
        ]);

        return response()->json([
            'status' => 'pending'
        ]);
    }

    /**
     * Logs the user out by deleting his specific token.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    function logout(Request $request){
        Token::where('token', $request->header('Authorization'))->delete();
        return response()->json([
            'success'=>'true',
            'message'=>'user logged out'
        ]);
    }
}
