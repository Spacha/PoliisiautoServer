<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * A lookup table for controllers in the project. Used to shorten
 * the otherwise long class name fetching in route definitions.
 */
$ctrl = [
    'Organization'  => OrganizationController::class,
    'Case'          => CaseController::class,
    'Report'        => ReportController::class,
    'ReportMessage' => ReportMessageController::class,
    'Student'       => StudentController::class,
    'Teacher'       => TeacherController::class,
    'Administrator' => AdministratorController::class,
];

Route::prefix('v1')->group(function () use ($ctrl) {

    // Public routes
    Route::get('/', function() {
        return view('welcome');
    });

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () use ($ctrl) {

        Route::post('logout', [AuthController::class, 'logout']);

        // Organization
        Route::get('organizations',                     [$ctrl['Organization'], 'index']);
        Route::get('organizations',                     [$ctrl['Organization'], 'show']);
        Route::post('organizations',                    [$ctrl['Organization'], 'store']);
        Route::patch('organizations/{id}',              [$ctrl['Organization'], 'update']);
        Route::delete('organizations/{id}',             [$ctrl['Organization'], 'destroy']);

        // Case
        Route::get('cases',                             [$ctrl['Case'], 'index']);
        Route::get('cases/{id}',                        [$ctrl['Case'], 'show']);
        Route::post('cases',                            [$ctrl['Case'], 'store']);
        Route::patch('cases/{id}',                      [$ctrl['Case'], 'update']);
        Route::delete('cases/{id}',                     [$ctrl['Case'], 'destroy']);
        Route::get('cases/{id}/reports',                [$ctrl['Case'], 'reports']);

        // Report
        Route::get('reports',                           [$ctrl['Report'], 'index']);
        Route::get('reports/{id}',                      [$ctrl['Report'], 'show']);
        Route::post('reports',                          [$ctrl['Report'], 'store']);
        Route::patch('reports/{id}',                    [$ctrl['Report'], 'update']);
        Route::delete('reports/{id}',                   [$ctrl['Report'], 'destroy']);
        Route::get('reports/{report_id}/messages',      [$ctrl['Report'], 'messages']);

        // Report message
        Route::get('report-messages/{id}',              [$ctrl['ReportMessage'], 'show']);
        Route::post('cases/{case_id}/messages',         [$ctrl['ReportMessage'], 'store']);
        Route::patch('report-messages/{id}',            [$ctrl['ReportMessage'], 'update']);
        Route::delete('report-messages/{id}',           [$ctrl['ReportMessage'], 'destroy']);

        // Student
        Route::get('students',                          [$ctrl['Student'], 'index']);
        Route::get('students/{id}',                     [$ctrl['Student'], 'show']);
        Route::post('students',                         [$ctrl['Student'], 'store']);
        Route::patch('students/{id}',                   [$ctrl['Student'], 'update']);
        Route::delete('students/{id}',                  [$ctrl['Student'], 'destroy']);
        Route::get('students/{id}/reports',             [$ctrl['Student'], 'reports']);

        // Teacher
        Route::get('teachers',                          [$ctrl['Teacher'],'index']);
        Route::get('teachers/{id}',                     [$ctrl['Teacher'],'show']);
        Route::post('teachers',                         [$ctrl['Teacher'],'store']);
        Route::patch('teachers/{id}',                   [$ctrl['Teacher'],'update']);
        Route::delete('teachers/{id}',                  [$ctrl['Teacher'],'destroy']);
        Route::get('teachers/{id}/assigned-reports',    [$ctrl['Teacher'],'assignedReports']);
        Route::get('teachers/{id}/reports',             [$ctrl['Teacher'],'reports']);

        // Administrator
        Route::get('administrators',                    [$ctrl['Administrator'],'index']);
        Route::get('administrators/{id}',               [$ctrl['Administrator'],'show']);
        Route::post('administrators',                   [$ctrl['Administrator'],'store']);
        Route::patch('administrators/{id}',             [$ctrl['Administrator'],'update']);
        Route::delete('administrators/{id}',            [$ctrl['Administrator'],'destroy']);
        
    });
});