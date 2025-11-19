<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ConducteurController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnveloppeController;
use App\Http\Controllers\ExcesController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Auth;
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

// Routes d'authentification
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    Route::post('/courses/enregistrer', [CourseController::class, 'enregistrer'])->name('courses.enregistrer');
    Route::post('/courses/commenter', [CourseController::class, 'commenter'])->name('courses.commenter');
    Route::get('/courses/nbcontroles', [CourseController::class, 'nbControles'])->name('courses.nbcontroles');
    Route::get('/courses/calendar/{year}/{month}', [CourseController::class, 'calendar'])->name('courses.calendar');
    Route::get('/courses/bydate/{ladate}', [CourseController::class, 'bydate'])->name('courses.bydate');
    Route::post('/courses/upload', [CourseController::class, 'upload'])->name('courses.upload');
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes des ressources
    Route::resource('conducteurs', ConducteurController::class);
    Route::resource('courses', CourseController::class);
    Route::resource('enveloppes', EnveloppeController::class);
    Route::resource('exces', ExcesController::class);

    // Route pour changer le site
    Route::post('/site/set', [SiteController::class, 'setSite'])->name('site.set');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});
