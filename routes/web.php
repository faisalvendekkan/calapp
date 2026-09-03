<?php

use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NfcRedirectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileAssetController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\QrRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/p/{slug}', PublicProfileController::class)->name('public.profile');
Route::post('/p/{slug}/enquiries', [LeadController::class, 'store'])->middleware('throttle:10,1')->name('public.leads.store');
Route::get('/p/{slug}/vcard', [PublicProfileAssetController::class, 'vcard'])->middleware('throttle:30,1')->name('public.vcard');
Route::get('/p/{slug}/qr.svg', [PublicProfileAssetController::class, 'qr'])->middleware('throttle:30,1')->name('public.qr');
Route::get('/p/{slug}/go/{action}', [PublicProfileAssetController::class, 'contact'])->middleware('throttle:120,1')->name('public.contact');
Route::get('/p/{slug}/social/{network}', [PublicProfileAssetController::class, 'social'])->middleware('throttle:120,1')->name('public.social');
Route::get('/q/{uuid}', QrRedirectController::class)->middleware('throttle:120,1')->name('qr.redirect');
Route::get('/n/{token}', NfcRedirectController::class)->middleware('throttle:120,1')->name('nfc.redirect');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('profiles', CustomerProfileController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
