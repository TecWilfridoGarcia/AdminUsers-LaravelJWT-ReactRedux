<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use JWTAuhth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuhth\Exceptions\JWTException;





class AuthController extends Controller
{
    protected $user;
    public function _constructor()
    {
        $this->user = new User;
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "success"=>false,
                "message"=>$validator->message()->toArray(),
            ], 400);
        }
        $check_email = $this->user->where("email", $request->email)->count();
        if($check_email > 0) {
            return response()->json([
                "success"=>false,
                "message"=> "This email already exist please try another email"
            ],200);
        }

        $registerComplete = $this->user::create([
            'firstname'=>$request->firstname,
            'lastname'=>$request->lastname,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        if($registerComplete) {
            $this->login($request);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->only('email', 'password'),
        [
            'email'=>'required|email',
            'password'=>'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "success"=>false,
                "message"=>$validator->message()->toArray(),
            ], 400);
        }
        $jwt_token = null;

        $input = $request->only("email", "password");
        if(!$jwt_token = auth('users')->attempt($input)) {
            return response()->json([
                "success"=>false,
                "message"=>"Invalid email or password"
            ]);
        }  
        return response()->json([
            'success'=>true,
            'token'=>$jwt_token
        ]);
    }
}
