<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

// Root / Landing
Route::get('/', [PublicPageController::class, 'index'])->name('public.landing');

// Core Institutional Pages
Route::get('/about', [PublicPageController::class, 'about'])->name('public.about');
Route::get('/how-it-works', [PublicPageController::class, 'howItWorks'])->name('public.how-it-works');

// Solutions
Route::prefix('solutions')->group(function () {
    Route::get('/patients', [PublicPageController::class, 'solutionsPatients'])->name('public.solutions.patients');
    Route::get('/hospitals', [PublicPageController::class, 'solutionsHospitals'])->name('public.solutions.hospitals');
    Route::get('/pharmacies', [PublicPageController::class, 'solutionsPharmacies'])->name('public.solutions.pharmacies');
    Route::get('/laboratories', [PublicPageController::class, 'solutionsLaboratories'])->name('public.solutions.laboratories');
    Route::get('/insurers', [PublicPageController::class, 'solutionsInsurers'])->name('public.solutions.insurers');
    Route::get('/public-health', [PublicPageController::class, 'solutionsPublicHealth'])->name('public.solutions.public-health');
});

// Tech & Dev
Route::get('/interoperability', [PublicPageController::class, 'interoperability'])->name('public.interoperability');
Route::get('/developers', [PublicPageController::class, 'developers'])->name('public.developers');
Route::get('/security', [PublicPageController::class, 'security'])->name('public.security');

// Legal & Privacy
Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('public.privacy');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('public.terms');
Route::get('/consent', [PublicPageController::class, 'consent'])->name('public.consent');

// Support
Route::get('/faq', [PublicPageController::class, 'faq'])->name('public.faq');
Route::get('/help', [PublicPageController::class, 'help'])->name('public.help');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('public.contact');
Route::get('/status', [PublicPageController::class, 'status'])->name('public.status');

// Onboarding / Auth
Route::get('/register', [PublicPageController::class, 'showRegisterSelector'])->name('register');
Route::get('/register/patient', [PublicPageController::class, 'showPatientRegister'])->name('register.patient');
Route::get('/register/hospital', [PublicPageController::class, 'showHospitalRegister'])->name('register.hospital');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Localization Switcher
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');
