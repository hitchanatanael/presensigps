<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login_admin');
    }

    public function auth(Request $request)
    {
        $credentials = $request->validate([
            'nip' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role == 'admin') {
                $request->session()->regenerate();
                $request->session()->put('id_user', $user->id_user);
                $request->session()->put('nip', $user->nip);
                $request->session()->put('role', $user->role);

                toastr()->success('Login berhasil!');

                return redirect()->route('dashboard_admin');
            } else {
                Auth::logout();
                toastr()->error('Hanya admin yang dapat login.');

                return back()->withErrors([
                    'loginError' => 'Hanya admin yang dapat login.'
                ]);
            }
        }

        toastr()->error('NIP atau password salah.');

        return back()->withErrors([
            'loginError' => 'NIP atau password salah.',
        ]);
    }
}
