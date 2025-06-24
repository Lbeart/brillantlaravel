<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Klienti;

class LoginController extends Controller
{
    // Shfaq forma e loginit
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proceson login-in
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Redirect sipas rolit
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'user') {
                return redirect()->route('user.dashboard');
            }

            // Nëse roli nuk është i njohur
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Roli i panjohur. Kontakto administratorin.',
            ]);
        }

        // Nëse login-i dështon
        return back()->withErrors([
            'email' => 'Email ose fjalëkalimi është gabim.',
        ]);
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    // Dashboard i përdoruesit (punëtorit)
    public function userDashboard()
    {
        $klientet = Klienti::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->get();

        return view('user.dashboard', compact('klientet'));
    }
}
