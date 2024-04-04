<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::get('/db-check', function () {
    try {
        DB::connection()->getPdo();
        return "Connected successfully to: " . DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "Could not connect to the database. Please check your configuration. error:" . $e;
    }
});

Route::get('/viewstaff', [UserController::class, 'index']);
Route::get('/createstaff', [UserController::class, 'create']);
Route::get('/getstaff', [UserController::class, 'getUsersDatatable'])->name('user.getAllDatatable');
Route::post('/storestaff', [UserController::class, 'store'])->name('user.store');
Route::post('/storeadmin', [UserController::class, 'storeAdmin'])->name('user.storeAdmin');
Route::get('/register', [UserController::class, 'register']);
Route::get('/login', [UserController::class, 'login']);
Route::post('/auth', [UserController::class, 'verifyUser']);




