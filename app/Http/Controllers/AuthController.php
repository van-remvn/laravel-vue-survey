<?php
/**
 * User: Zura
 * Date: 12/19/2021
 * Time: 3:49 PM
 */

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\Rules\Password;

/**
 * Class AuthController
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            // 'name' => 'required|string',
            // 'email' => 'required|email|string|unique:users,email',
            // 'password' => [
            //     'required',
            //     'confirmed',
            //     Password::min(8)->mixedCase()->numbers()->symbols()
            // ]
            'email' => 'required|string|unique:users,email',
            'password' => [
                'bail', 'required', Password::min(8), 'regex:/^[a-zA-Z0-9.*?\[#?!@$%^&*-]+$/u'
            ],
        ]);

        /** @var \App\Models\User $user */
        $user = User::create([
            // 'name' => $data['name'],
            'name' => $data['email'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);
        // $token = $user->createToken('main')->plainTextToken;

        // return response([
        //     'user' => $user,
        //     'token' => $token
        // ]);

        return response([
            'success' => true
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            // 'email' => 'required|email|string|exists:users,email',
            'email' => 'required|string|exists:users,email',
            'password' => [
                'required',
            ],
            'remember' => 'boolean'
        ],['email.exists' => 'Username is not correct!']);
        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);

        if (!Auth::attempt($credentials, $remember)) {
            return response([
                // 'error' => 'The Provided credentials are not correct'
                'errors' => 'Password is not correct!'
            ], 422);
        }
        $user = Auth::user();
        $token = $user->createToken('main')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout()
    {
        /** @var User $user */
        $user = Auth::user();
        // Revoke the token that was used to authenticate the current request...
        $user->currentAccessToken()->delete();

        return response([
            'success' => true
        ]);
    }

    public function updatePoint(Request $request)
    {
        // $user = Auth::user();

        // dd($user);

        $updateSuccess = User::where('id', $request->id)->update([
            'point' => $request->point,
        ]);

        if ($updateSuccess) {
            return response([
                'success' => true
            ]);
        }
    }

}
