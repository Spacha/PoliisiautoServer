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

        // Organizations
        Route::get('organizations',                     [$ctrl['Organization'],     'index']);
        Route::get('organization/{id}',                 [$ctrl['Organization'],     'show']);
        Route::post('organizations',                    [$ctrl['Organization'],     'store']);
        Route::patch('organizations',                   [$ctrl['Organization'],     'update']);
        Route::delete('organizations/{id}',             [$ctrl['Organization'],     'destroy']);
        
        //Route::get('organizations/{id}/cases',          [$ctrl['Organization'], 'cases']);
        //Route::get('organizations/{id}/reports',        [$ctrl['Organization'], 'reports']);
        //Route::get('organizations/{id}/students',       [$ctrl['Organization'], 'students']);
        //Route::get('organizations/{id}/teachers',       [$ctrl['Organization'], 'teachers']);
        //Route::get('organizations/{id}/administrators', [$ctrl['Organization'], 'administrators']);

        // Cases
        Route::get('cases',                             [$ctrl['Case'],             'index']);
        //Route::post('cases',                            [$ctrl['Case'],           'store']);
        Route::get('cases/{id}',                        [$ctrl['Case'],             'show']);
        Route::patch('cases/{id}',                      [$ctrl['Case'],             'update']);
        Route::delete('cases/{id}',                     [$ctrl['Case'],             'destroy']);

        Route::get('cases/{id}/reports',                [$ctrl['Case'],             'reports']);

        // Reports
        Route::get('reports',                           [$ctrl['Report'],           'index']);
        Route::get('reports/{id}',                      [$ctrl['Report'],           'show']);
        Route::post('reports',                          [$ctrl['Report'],           'store']);
        Route::patch('reports/{id}',                    [$ctrl['Report'],           'update']);
        Route::delete('reports/{id}',                   [$ctrl['Report'],           'destroy']);

        // ReportMessages
        Route::get('reports/{report_id}/messages',      [$ctrl['ReportMessage'],    'index']);
        Route::post('reports/{report_id}/messages',     [$ctrl['ReportMessage'],    'store']);
        Route::get('report-messages/{id}',              [$ctrl['ReportMessage'],    'show']);
        Route::patch('report-messages/{id}',            [$ctrl['ReportMessage'],    'update']);
        Route::delete('report-messages/{id}',           [$ctrl['ReportMessage'],    'destroy']);

        // Students
        Route::get('students',                          [$ctrl['Student'],          'index']);
        Route::post('students',                         [$ctrl['Student'],          'store']);
        Route::get('students/{id}',                     [$ctrl['Student'],          'show']);
        Route::patch('students/{id}',                   [$ctrl['Student'],          'update']);
        Route::delete('students/{id}',                  [$ctrl['Student'],          'destroy']);

        // Teachers
        Route::get('teachers',                          [$ctrl['Teacher'],          'index']);
        Route::post('teachers',                         [$ctrl['Teacher'],          'store']);
        Route::get('teachers/{id}',                     [$ctrl['Teacher'],          'show']);
        Route::patch('teachers/{id}',                   [$ctrl['Teacher'],          'update']);
        Route::delete('teachers/{id}',                  [$ctrl['Teacher'],          'destroy']);

        // Administrators
        Route::get('administrators',                    [$ctrl['Administrator'],    'index']);
        Route::post('administrators',                   [$ctrl['Administrator'],    'store']);
        Route::get('administrators/{id}',               [$ctrl['Administrator'],    'show']);
        Route::patch('administrators/{id}',             [$ctrl['Administrator'],    'update']);
        Route::delete('administrators/{id}',            [$ctrl['Administrator'],    'destroy']);

        /*
        // Organizations: list, show, store, update, delete
        Route::apiResource('organizations', OrganizationController::class)->shallow();

        // Teachers: list, show, store, update, delete, list assigned reports
        Route::apiResource('organizations.teachers', TeacherController::class)->shallow();
        Route::get('teachers/{teacher_id}/assigned-reports', [TeacherController::class, 'assigned-reports']);

        // Students: list, show, store, update, delete, list created reports, list targeted reports
        Route::apiResource('organizations.students', StudentController::class)->shallow();
        Route::get('students/{student_id}/created-reports', [StudentController::class, 'created-reports']);
        Route::get('students/{student_id}/targeted-reports', [StudentController::class, 'targeted-reports']);

        // Reports: list, show, store, update, delete
        Route::apiResource('organizations.reports', ReportController::class)->shallow();
        */
    });
});