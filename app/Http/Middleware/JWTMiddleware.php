<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JWTMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            $token = $request->bearerToken();

            if(!$token) {
                // Unauthorized response if token not there
                return [
                    'status' => 401,
                    'error' => 'Not Authorized'
                ];
            }

            $credentials = JWT::decode($token, env('JWT_SECRET', 'secret'), ['HS256']);
            $request->user = $credentials->user;
            
        } catch(ExpiredException $e) {
            return [
                'code' => 400,
                'error' => 'Provided token is expired.'
            ];
        } catch(\Exception $e) {
            return [
                'code' => 400,
                'error' => 'An error while decoding token.'
            ];
        }
        
        return $next($request);
    }
}