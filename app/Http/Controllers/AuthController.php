<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $user = User::where('email', $request->email)->first();
            if (!$user) return response()->json('Email not found', 404);

            if (Hash::check($request->password, $user->password)) {

                $payload = [
                    'iss' => 'Organization', // Issuer of the token, Organization / Product
                    'sub' => 'subject', // Subject of the token
                    'iat' => time(), // Time when JWT was issued.
                    //'exp' => time() + 60*60, // Expiration time
                    'user' => $user
                ];
                $jwt = JWT::encode($payload, env('JWT_SECRET', 'secret'));
                $user->token = $jwt;

                return response()->json($user);

            } else {
                throw new Exception('Password not valid');
            }

        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }
}