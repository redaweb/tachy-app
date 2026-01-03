<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ConducteurController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnveloppeController;
use App\Http\Controllers\ExcesController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StatFreinageController;
use App\Http\Controllers\StatistiqueController;
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
    route::get('/courses/depouillement/{idcourse}', [CourseController::class, 'depouillement'])->name('courses.depouillement');
    Route::post('/courses/upload', [CourseController::class, 'upload'])->name('courses.upload');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes des ressources
    Route::resource('conducteurs', ConducteurController::class);
    Route::resource('courses', CourseController::class);
    Route::resource('exces', ExcesController::class);

    // Statistiques
    Route::prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('categories', [StatistiqueController::class, 'categories'])->name('categories');
        Route::get('evolution', [StatistiqueController::class, 'evolution'])->name('evolution');
        Route::get('conducteurs', [StatistiqueController::class, 'conducteurs'])->name('conducteurs');
        Route::get('interstations', [StatistiqueController::class, 'interstations'])->name('interstations');
        Route::get('mensuelle', [StatistiqueController::class, 'mensuelle'])->name('mensuelle');

        // API endpoints
        Route::get('api/exces', [StatistiqueController::class, 'apiExces'])->name('api.exces');
        Route::get('export-csv', [StatistiqueController::class, 'exportCSV'])->name('export-csv');
    });

    // Statistiques Freinage
    Route::prefix('stat-freinages')->name('stat-freinages.')->group(function () {
        Route::get('categories', [StatFreinageController::class, 'categories'])->name('categories');
        Route::get('evolution', [StatFreinageController::class, 'evolution'])->name('evolution');
        Route::get('conducteurs', [StatFreinageController::class, 'conducteurs'])->name('conducteurs');
        Route::get('interstations', [StatFreinageController::class, 'interstations'])->name('interstations');
        Route::get('api/freinage', [StatFreinageController::class, 'apiFreinage'])->name('api.freinage');
        Route::get('export-csv', [StatFreinageController::class, 'exportCSV'])->name('export-csv');
    });
    // Routes Enveloppes (avec routes supplémentaires)
    Route::prefix('enveloppes')->name('enveloppes.')->group(function () {
        Route::get('/', [EnveloppeController::class, 'index'])->name('index');
        Route::post('/', [EnveloppeController::class, 'store'])->name('store');
        Route::delete('/{id}', [EnveloppeController::class, 'destroy'])->name('destroy');
        Route::post('/toggle-freeze', [EnveloppeController::class, 'toggleFreeze'])->name('toggle-freeze');
        Route::get('/read', [EnveloppeController::class, 'readEnvelope'])->name('read');
    });

    // Route pour changer le site
    Route::post('/site/set', [SiteController::class, 'setSite'])->name('site.set');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});
