<?php

namespace App\Http\Controllers;

use Appoly\LaravelApiPasswordHelper\Http\Notifications\ResetPassword;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator;
use App\User;

class JWTAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','forgot','reset']]);
    }

    public function forgot(Request $request)
    {
        if ($request->has('email')) {
            $password_helper_key = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);


            $user = \App\User::where('email', $request->email)
                ->orWhere('email', urldecode($request->email))
                ->first();

            if ($user) {
                $user->password_helper_key = $password_helper_key;
                $user->save();

                $user->notify(new ResetPassword($user));

                return response()->json(
                    ['message' => 'An email has been sent to your account'],
                    200);
            } else {
                return response()->json(
                    ['message' => 'No user found'],
                    404);
            }
        }

        return response()->json(['message' => 'Email is required'], 404);
    }

    public function reset(Request $request)
    {
        if ($request->has('key') && $request->has('password')) {
            $user = \App\User::where('password_helper_key', $request->key)->first();

            if ($user) {
                $user->update([
                    'password' => bcrypt($request->password),
                    'password_helper_key' => null,
                ]);

                return response()->json(
                    ['message' => 'Your password has been updated'], 200);
            } else {
                return response()->json([
                    ['message' => 'Invalid reset code'],
                ], 404);
            }
        }

        return response()->json([
            ['message' => 'Key and Password are required'],
        ], 400);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|between:2,100',
            'email' => 'required|email|unique:users|max:50',
            'password' => 'required|confirmed|string|min:6',
        ]);

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'Successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    public function profile()
    {
        return 'all';
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
