<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', [PageController::class, 'home'])->name('page.home');
Route::get('/login', [AuthController::class, 'login'])->name('page.login');
Route::get('/register', [AuthController::class, 'signup'])->name('page.register');

// Auth
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth', [AuthController::class, 'authenticate'])->name('auth.login');
Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');