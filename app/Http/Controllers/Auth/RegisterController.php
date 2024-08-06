<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ]);
    }

    protected function create(array $data)
    {
        // Do not create user in the database yet
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $otp = Str::random(6);

        // Store user data temporarily
        $request->session()->put('registration_data', [
            'name' => $request->name,
            'email' => $request->email,
            'otp' => $otp
        ]);

        // Send OTP email
        Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
            $message->to($request->email)->subject('OTP Verification');
        });

        return redirect('/verify-otp')->with('email', $request->email);
    }

    public function showOtpForm()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'email' => 'required|email'
        ]);

        $sessionData = $request->session()->get('registration_data');

        if ($sessionData && $sessionData['otp'] === $request->otp && $sessionData['email'] === $request->email) {
            // Create user in the database
            $password = Str::random(8);
            User::create([
                'name' => $sessionData['name'],
                'email' => $sessionData['email'],
                'password' => Hash::make($password),
            ]);

            // Clear session data
            $request->session()->forget('registration_data');

            // Send login password email
            Mail::raw("Your login password is: $password", function ($message) use ($sessionData) {
                $message->to($sessionData['email'])->subject('Login Password');
            });

            return redirect('/login')->with('status', 'OTP verified. Check your email for the login password.');
        } else {
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }
    }
}
