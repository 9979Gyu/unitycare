<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostcodeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChartController;

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

Route::get('/', [LandingController::class, 'index']);
Route::get('/info', [LandingController::class, 'info']);

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

// Export Excel Route
Route::post('/export-users', [UserController::class, 'exportUsers'])->name('export-users');
Route::post('/export-programs', [ProgramController::class, 'exportPrograms']);
Route::post('/export-jobs', [JobController::class, 'exportJobs']);
Route::post('/export-offers', [OfferController::class, 'exportOffers']);
Route::post('/export-applications', [ApplicationController::class, 'exportApplications']);
Route::post('/export-participants', [ParticipantController::class, 'exportParticipants']);
Route::post('/export-specs', [ReportController::class, 'exportProgramsWithSpecs']);
Route::post('/export-applies', [ApplicationController::class, 'exportApplied']);


Route::get('/create', [UserController::class, 'create']);
Route::post('/store', [UserController::class, 'store']);
Route::get('/verifyEmail', [UserController::class, 'confirmEmail']);
Route::get('/view/{roleNo}', [UserController::class, 'index']);
Route::get('/getstaff', [UserController::class, 'getUsersDatatable'])->name('user.getAllDatatable');
Route::get('/edituser/{id}', [UserController::class, 'edit']);
Route::post('/updateuser', [UserController::class, 'update']);
Route::post('/deleteuser', [UserController::class, 'destroy']);
Route::get('/set-password', [UserController::class, 'indexChangePassword']);
Route::post('/change-password', [UserController::class, 'changePassword']);
Route::post('/reset', [UserController::class, 'changePasswordEmail']);
Route::get('/login-reset', [UserController::class, 'resetPassword']);

Route::get('/register', [UserController::class, 'register']);

// Postcode - search state and cities
Route::get('/searchPostcode', [PostcodeController::class, 'search']);
Route::get('/getCityState', [PostcodeController::class, 'getCityState']);

// Poor people
Route::get('/createspecial', [UserController::class, 'createPoorPeople']);
Route::post('/check-user', [UserController::class, 'checkUser']);
Route::get('/viewprofile', [UserController::class, 'indexProfile']);

// Activity / Program
Route::get('/viewprogram', [ProgramController::class, 'index']);
Route::get('/createprogram/{roleNo}', [ProgramController::class, 'create']);
Route::post('/storeprogram', [ProgramController::class, 'store']);
Route::get('/getprogram', [ProgramController::class, 'getProgramsDatatable']);
Route::get('/getProgramById', [ProgramController::class, 'getProgramById']);
Route::get('/editprogram/{id}', [ProgramController::class, 'edit']);
Route::post('/updateprogram', [ProgramController::class, 'update']);
Route::post('/approveprogram', [ProgramController::class, 'updateApproval']);
Route::post('/declineprogram', [ProgramController::class, 'declineApproval']);
Route::post('/deleteprogram', [ProgramController::class, 'destroy']);
Route::get('/getUpdatedPrograms', [ProgramController::class, 'getUpdatedPrograms']);

Route::get('/joinprogram/{id}', [ParticipantController::class, 'create']);
Route::post('/dismissprogram', [ParticipantController::class, 'dismiss']);
Route::post('/storeparticipant', [ParticipantController::class, 'store']);
Route::get('/indexparticipant', [ParticipantController::class, 'index']);
Route::get('/getparticipants', [ParticipantController::class, 'getParticipantsDatatable']);

// Jobs
Route::get('/createjob', [JobController::class, 'create']);
Route::get('/viewjob', [JobController::class, 'index']);
Route::get('/getjob', [JobController::class, 'getJobsDatatable']);
Route::post('/storejob', [JobController::class, 'store']);

// Offers
Route::get('/viewoffer', [OfferController::class, 'index']);
Route::get('/getJobsByUser', [OfferController::class, 'getJobsByUser']);
Route::get('/getPositions', [OfferController::class, 'getPositions']);
Route::get('/getoffersbyposition', [OfferController::class, 'getOffersByPositionDatatable']);

Route::get('/createoffer', [OfferController::class, 'create']);
Route::get('/getAllJobs', [OfferController::class, 'getAllJobs']);
Route::get('/getAllPositions', [OfferController::class, 'getAllPositions']);
Route::post('/storeoffer', [OfferController::class, 'store']);

Route::get('/editoffer/{id}', [OfferController::class, 'edit']);
Route::post('/updateoffer', [OfferController::class, 'update']);

Route::post('/approval', [OfferController::class, 'updateApproval']);
Route::post('/deleteoffer', [OfferController::class, 'destroy']);
Route::get('/getUpdatedOffers', [OfferController::class, 'getUpdatedOffers']);


Route::get('/joinoffer/{id}', [ApplicationController::class, 'create']);
Route::post('/dismissoffer', [ApplicationController::class, 'dismiss']);
Route::post('/storeapplication', [ApplicationController::class, 'store']);
Route::post('/deleteApplication', [ApplicationController::class, 'destroy']);
Route::get('/getApplications', [ApplicationController::class, 'getApplicationsDatatable']);
Route::get('/viewapplication', [ApplicationController::class, 'index']);
Route::get('/getApplicationsByCondition', [ApplicationController::class, 'getApplicationsByCondition']);
Route::post('/updateApproval', [ApplicationController::class, 'updateApproval']);
Route::post('/confirmOffer', [ApplicationController::class, 'confirmOffer']);


Route::get('/getPrograms', [LandingController::class, 'getPrograms']);
Route::get('/search', [LandingController::class, 'search']);
// Route::get('/getJobs', [LandingController::class, 'getJobs']);
// Route::get('/getCountPosition', [LandingController::class, 'getCountPosition']);
// Route::get('/getCountOffer', [LandingController::class, 'getCountOffer']);

// Report
Route::get('/getProgramsWithSpecs', [ReportController::class, 'getAllProgramsDetailsDatatable']);
Route::get('/index-programs', [ReportController::class, 'indexPrograms']);
Route::get('/index-offers', [ReportController::class, 'indexOffers']);

Route::get('/bar-chart', [ChartController::class, 'barChart']);
Route::get('/user-pie-chart', [ChartController::class, 'peoplePieChart']);
Route::get('/offer-bar-chart', [ChartController::class, 'offerBarChart']);
Route::get('/app-bar-chart', [ChartController::class, 'appBarChart']);