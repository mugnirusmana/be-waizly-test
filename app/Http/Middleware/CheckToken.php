<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        $force_logout = $request->header('Force-Logout');

        if (!$token) return setRes(null, 401);

        $token = explode(' ', $token)[1];

        $decode_token = decryptToken($token);
        if($decode_token === 'error') return setRes(null, 401, 'Unauthorized - Invalid token');

        if ($force_logout !== "yes") {
            $isExpired = isTokenExpired($decode_token->expired_until);
            if($isExpired) return setRes(null, 401, 'Unauthorized - Token has expired');
    
            $data = User::where('token', $token)->first();
            if(!$data) return setRes(null, 401, 'Unauthorized - Your token is invalid or you were login on another session');
        }

        return $next($request);
    }
}