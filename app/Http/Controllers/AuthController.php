<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Hash;
use DB;
use Crypt;

use App\Models\User;

class AuthController extends Controller
{
    function login(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'username' => ['required'],
                'password' => ['required'],
            ], [
                'username.required' => 'Email or Username is required',
                'password.required' => 'Password is required',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                $result = setValidationMessage($validator, ['username', 'password']);
                return setRes($result, 400);
            }

            $data = User::where('email', $request->username)->orWhere('username', $request->username)->first();

            if (!$data) {
                DB::rollback();
                return setRes(null, 400, 'Your Email / Username and Password does not match');
            }

            if (!$data->is_verified) {
                DB::rollback();
                return setRes(null, 400, 'Your account is not active yet');
            }

            $checkPassword = Hash::check($request->password, $data->password);
            if (!$checkPassword) {
                DB::rollback();
                return setRes(null, 400, 'Your Email / Username and Password combination is does not match');
            }

            $date = Carbon::now();
            $date->addDays(5);
            $data['expired_until'] = $date;
            $token = encryptToken($data);
            $data['access_token'] = $token;
            unset($data['expired_until']);

            $update_user = User::find($data->id);
            $update_user->token = $token;
            $update_user->save();

            DB::commit();
            return setRes($data, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function register(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'email' => ['required', 'unique:users', 'email'],
                'username' => ['required', 'unique:users', 'regex:/^[a-zA-Z][0-9]+$/'],
                'password' => ['required'],
            ], [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.unique' => 'Email has been taken, try another email',
                'email.email' => 'Email format is not valid',
                'username.required' => 'Username is required',
                'username.unique' => 'Username has been taken, try another username',
                'username.regex' => 'Username format is not valid, only alphabet and numuric, first letter must be alphabet',
                'password.required' => 'Password is required',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                $result = setValidationMessage($validator, ['name', 'email', 'username', 'password']);
                return setRes($result, 400);
            }

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_verified' => true,
            ]);

            DB::commit();
            return setRes(null, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }
}
