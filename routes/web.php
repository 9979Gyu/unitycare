<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostcodeController;

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
    // return view('welcome');
    return view('/landings/index');
});

Route::get('/db-check', function () {
    try {
        DB::connection()->getPdo();
        return "Connected successfully to: " . DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "Could not connect to the database. Please check your configuration. error:" . $e;
    }
});

Route::get('/login', function () {
    return view('/auths/login');
});

Route::post('/auth', [UserController::class, 'verifyUser']);
Route::post('/logout', [UserController::class, 'logout']);


Route::get('/create/{roleNo}', [UserController::class, 'create']);
Route::post('/store', [UserController::class, 'store']);

Route::get('/view/{roleNo}', [UserController::class, 'index']);

Route::get('/getstaff', [UserController::class, 'getUsersDatatable'])->name('user.getAllDatatable');

Route::post('/storeadmin', [UserController::class, 'storeAdmin'])->name('user.storeAdmin');
Route::get('/register', [UserController::class, 'register']);

Route::get('/edituser/{id}', [UserController::class, 'edit']);
Route::post('/updateuser/{id}', [UserController::class, 'update']);
Route::post('/deleteuser/{id}', [UserController::class, 'destroy']);


// Volunteer


// Postcode - search state and cities
Route::get('/search', [PostcodeController::class, 'search']);

