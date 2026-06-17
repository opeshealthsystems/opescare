<?php

use App\Http\Controllers\Marketing\PricingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing / growth public routes
|--------------------------------------------------------------------------
| Loaded with the 'web' middleware group from AppServiceProvider so it does
| not collide with the sealed routes/web.php during concurrent work.
*/

Route::get('/pricing', [PricingController::class, 'index'])->name('public.pricing');
