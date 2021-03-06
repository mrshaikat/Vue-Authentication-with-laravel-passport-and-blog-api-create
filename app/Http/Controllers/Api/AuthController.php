<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function index()
    {
        $all_user = User::all();
        return send_response('Success', $all_user);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [

            "email" => "required|email",
            "password" => "required",
        ]);

        if ($validator->fails()) {

            return send_error('Validation Error', $validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            $data['name'] = $user->name;
            $data['access_token'] = $user->createToken('accessToken')->accessToken;

            return send_response('You are successfully login', $data);
        } else {
            return send_error('Unauthroised', '', 401);
        }
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|min:4",
            "email" => "required|email|unique:users",
            "password" => "required|min:6",
        ]);

        if ($validator->fails()) {

            return send_error('Validation Error', $validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $data = [
                'name' => $user->name,
                'email' => $user->email,
            ];
            return send_response('User registration successfull', $data);
        } catch (Exception $e) {

            return send_error('Something wrong !', $e->getCode());
        }
    }


    /**
     * Logout
     */
    public function logout(Request $request)
    {
        auth()->user()->token()->revoke();

        return response()->json(['message' => 'Successfully Logged out']);
    }



    public function show($id)
    {
        $user = User::find($id);

        if ($user) {
            return send_response('Success', $user);
        } else {
            return send_error('Data not found');
        }
    }
}