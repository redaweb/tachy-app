<?php

use App\Http\Controllers\ConducteurController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnveloppeController;
use App\Http\Controllers\ExcesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Routes des ressources
Route::resource('conducteurs', ConducteurController::class);
Route::resource('courses', CourseController::class);
Route::resource('enveloppes', EnveloppeController::class);
Route::resource('exces', ExcesController::class);
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
