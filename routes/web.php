<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Services\CustomMailer;

use Illuminate\Support\Facades\Mail;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/test', function () {
    return view('test');
});

// OTP Verification Routes
Route::get('/verify-otp', [RegisterController::class, 'showOtpForm'])->name('verify.otp.form');
Route::post('/verify-otp', [RegisterController::class, 'verifyOtp'])->name('verify.otp');



Route::get('/test-email', function (CustomMailer $mailer) {
    return $mailer->send('khusidaring777@gmail.com', 'Test Subject', 'This is the body of the email.');
});