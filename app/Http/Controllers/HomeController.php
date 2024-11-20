<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index_login()
    {
        // dd('test');
        return view('auth.login');
    }

    public function auth_login(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);;

        $query = User::where('email', $request->email)
            ->where('password', $request->password)
            ->first();

        if ($query) {
            $user = User::find($query->user_id);
            Auth::login($user);
            return redirect()->intended('/dashboard')
                ->withSuccess('Signed in');
            //         ->withSuccess('Signed in');
        } else {
            return Redirect::back()->withErrors(['msg' => 'Username/password yang dimasukkan salah']);
        }
    }

    public function index_registration()
    {
        return view('auth.registration');
    }

    public function auth_registration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);

        // dd($request->all());

        $data = $request->all();
        $newUser = $this->create($data);

        auth()->login($newUser);

        // dd(Auth::user()->name);
        return redirect("/dashboard_taksasi")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' =>  Hash::make($data['password']),
        ]);
    }

    public function dashboard()
    {
        if (Auth::check()) {
            return view('dashboard');
        }

        return redirect("login")->withSuccess('You are not allowed to access');
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return Redirect('/');
    }
}
