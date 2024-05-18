<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostcodeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\JobController;

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

Route::get('/db2-check', function(){
    try{
        // $dbVersion = DB::connection('mysqlSecondConnection')->table('users')->get();
        return "Connected successfully to: " . DB::connection("mysqlSecondConnection")->getDatabaseName();
    }
    catch (\Exception $e) {
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
Route::get('/edituser/{id}', [UserController::class, 'edit']);
Route::post('/updateuser/{id}', [UserController::class, 'update']);
Route::post('/deleteuser/{id}', [UserController::class, 'destroy']);

Route::get('/register', [UserController::class, 'register']);
Route::post('/storeadmin', [UserController::class, 'storeAdmin'])->name('user.storeAdmin');

// Postcode - search state and cities
Route::get('/search', [PostcodeController::class, 'search']);

// Poor people
Route::get('/createspecial', [UserController::class, 'createPoorPeople']);
Route::post('/checkUser', [UserController::class, 'checkUser']);

// Activity / Program
Route::get('/viewprogram', [ProgramController::class, 'index']);
Route::get('/createprogram/{roleNo}', [ProgramController::class, 'create']);
Route::post('/storeprogram', [ProgramController::class, 'store']);
Route::get('/getprogram', [ProgramController::class, 'getProgramsDatatable']);
Route::get('/getProgramById', [ProgramController::class, 'getProgramById']);
Route::get('/editprogram/{id}', [ProgramController::class, 'edit']);
Route::post('/updateprogram/{id}', [ProgramController::class, 'update']);
Route::post('/approveprogram/{id}', [ProgramController::class, 'updateApproval']);
Route::post('/declineprogram', [ProgramController::class, 'declineApproval']);
Route::post('/deleteprogram', [ProgramController::class, 'destroy']);
Route::get('/getUpdatedPrograms', [ProgramController::class, 'getUpdatedPrograms']);

Route::get('/joinprogram/{id}', [ParticipantController::class, 'create']);
Route::post('/dismissprogram', [ParticipantController::class, 'dismiss']);
Route::post('/storeparticipant', [ParticipantController::class, 'store']);

// Jobs
Route::get('/createjob', [JobController::class, 'create']);
Route::get('/viewjob', [JobController::class, 'index']);
Route::get('/getjob', [JobController::class, 'getJobsDatatable']);



