<?php

use App\Http\Controllers\Admin\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/me', [SessionController::class, 'show'])->name('me');
