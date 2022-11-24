<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers;

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
    'Auth'          => Controllers\AuthController::class,
    'Organization'  => Controllers\OrganizationController::class,
    'Case'          => Controllers\ReportCaseController::class,
    'Report'        => Controllers\ReportController::class,
    'ReportMessage' => Controllers\ReportMessageController::class,
    'Student'       => Controllers\StudentController::class,
    'Teacher'       => Controllers\TeacherController::class,
    'Administrator' => Controllers\AdministratorController::class,
];

Route::prefix('v1')->group(function () use ($ctrl) {

    // Public routes
    Route::get('/', function() {
        return view('welcome');
    })->name('home');

    Route::post('register', [$ctrl['Auth'], 'register']);
    Route::post('login', [$ctrl['Auth'], 'login']);

    // Protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () use ($ctrl) {

        // Auth routes
        Route::post('logout', [$ctrl['Auth'], 'logout']);
        Route::get('profile', [$ctrl['Auth'], 'profile']);
        Route::get('profile/organization', [$ctrl['Auth'], 'organization']);

        // Organization
        Route::get('organizations',                     [$ctrl['Organization'], 'index']);          //< list all organizations; accessible by: no one
        Route::post('organizations',                    [$ctrl['Organization'], 'store']);          //< store a new organization; accessible by: no one
        Route::get('organizations/{id}',                [$ctrl['Organization'], 'show']);           //< show an organization; accessible by: by anyone
        Route::patch('organizations/{id}',              [$ctrl['Organization'], 'update']);         //< update an organization; accessible by: administrators
        Route::delete('organizations/{id}',             [$ctrl['Organization'], 'destroy']);        //< delete an organization; accessible by: no one

        // Case
        Route::get('cases',                             [$ctrl['Case'], 'index']);                  //< list all cases in the organization; accessible by: teachers
        Route::post('cases',                            [$ctrl['Case'], 'store']);                  //< store a new case; accessible by: students
        Route::get('cases/{id}',                        [$ctrl['Case'], 'show']);                   //< show a case; accessible by: teachers
        Route::patch('cases/{id}',                      [$ctrl['Case'], 'update']);                 //< update a case; accessible by: teachers
        Route::delete('cases/{id}',                     [$ctrl['Case'], 'destroy']);                //< delete a case; accessible by: teachers
        Route::get('cases/{id}/reports',                [$ctrl['Case'], 'reports']);                //< list all reports in the case; accessible by: teachers

        // Report
        Route::get('reports',                           [$ctrl['Report'], 'index']);                //< list all reports in the organization; accessible by: teachers
        Route::post('cases/{case_id}/reports',          [$ctrl['Report'], 'store']);                //< store a new report; accessible by: teachers + student (reporter)
        Route::get('reports/{id}',                      [$ctrl['Report'], 'show']);                 //< show a report; accessible by: teachers + student (reporter)
        Route::patch('reports/{id}',                    [$ctrl['Report'], 'update']);               //< update a report; accessible by: teachers + student (reporter)
        Route::delete('reports/{id}',                   [$ctrl['Report'], 'destroy']);              //< delete a report; accessible by: teachers + student (reporter)
        Route::get('reports/{id}/messages',             [$ctrl['Report'], 'messages']);             //< list all report messages in the report; accessible by: teachers
        Route::put('reports/{id}/update-case',          [$ctrl['Report'], 'updateCase']);           //< update the case of the report; accessible by: teachers
        Route::post('reports',                          [$ctrl['Report'], 'storeToNewCase']);       //< store a new report to a new case; accessible by: teachers + student (reporter)

        // Report message
        Route::post('reports/{report_id}/messages',     [$ctrl['ReportMessage'], 'store']);         //< store a new report message to the report; accessible by: teachers + student (reporter)
        Route::get('report-messages/{id}',              [$ctrl['ReportMessage'], 'show']);          //< show a report message; accessible by: teachers + student (reporter)
        Route::patch('report-messages/{id}',            [$ctrl['ReportMessage'], 'update']);        //< update a report message; accessible by: teachers + student (reporter)
        Route::delete('report-messages/{id}',           [$ctrl['ReportMessage'], 'destroy']);       //< delete a report message; accessible by: teachers + student (reporter)

        // Student
        Route::get('students',                          [$ctrl['Student'], 'index']);               //< list all students in the organization; accessible by: teachers
        Route::post('students',                         [$ctrl['Student'], 'store']);               //< store a new student to the organization; accessible by: teachers
        Route::get('students/{id}',                     [$ctrl['Student'], 'show']);                //< show a student; accessible by: teachers + student (self)
        Route::patch('students/{id}',                   [$ctrl['Student'], 'update']);              //< update a student; accessible by: students + student (self)
        Route::delete('students/{id}',                  [$ctrl['Student'], 'destroy']);             //< delete a student; accessible by: administrators
        Route::get('students/{id}/reports',             [$ctrl['Student'], 'reports']);             //< list all reports the student has created; accessible by: teachers + student (self)
        Route::get('students/{id}/involved-reports',    [$ctrl['Student'], 'involvedReports']);     //< list all reports the student is involved in; accessible by: teachers

        // Teacher
        Route::get('teachers',                          [$ctrl['Teacher'], 'index']);               //< list all teachers in the organization; accessible by: by anyone
        Route::post('teachers',                         [$ctrl['Teacher'], 'store']);               //< store a new teacher; accessible by: administrators
        Route::get('teachers/{id}',                     [$ctrl['Teacher'], 'show']);                //< show a teacher; accessible by: by anyone
        Route::patch('teachers/{id}',                   [$ctrl['Teacher'], 'update']);              //< update a teacher; accessible by: administrators + teacher (self)
        Route::delete('teachers/{id}',                  [$ctrl['Teacher'], 'destroy']);             //< delete a teacher; accessible by: administrators
        Route::get('teachers/{id}/reports',             [$ctrl['Teacher'], 'reports']);             //< list all reports the teacher has created; accessible by: teachers
        Route::get('teachers/{id}/assigned-reports',    [$ctrl['Teacher'], 'assignedReports']);     //< list all reports assigned to the teacher; accessible by: teachers

        // Administrator
        Route::get('administrators',                    [$ctrl['Administrator'], 'index']);         //< list all administrators in the organization; accessible by: administrators
        Route::post('administrators',                   [$ctrl['Administrator'], 'store']);         //< store a new administrator; accessible by: administrators
        Route::get('administrators/{id}',               [$ctrl['Administrator'], 'show']);          //< show an administrator; accessible by: administrators
        Route::patch('administrators/{id}',             [$ctrl['Administrator'], 'update']);        //< update an administrator; accessible by: administrators
        Route::delete('administrators/{id}',            [$ctrl['Administrator'], 'destroy']);       //< delete an administrator; accessible by: administrators
    });
});