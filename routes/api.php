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

Route::prefix('v1')->group(function () {

    // Public routes
    Route::get('/', function() {
        return view('welcome');
    });

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', [AuthController::class, 'logout']);

        // Organisations: list, show, store, update, delete
        Route::apiResource('organisations', OrganisationController::class)->shallow();

        // Teachers: list, show, store, update, delete, list assigned reports
        Route::apiResource('organisations.teachers', TeacherController::class)->shallow();
        Route::get('teachers/{teacher_id}/assigned-reports', [TeacherController::class, 'assigned-reports']);

        // Students: list, show, store, update, delete, list created reports, list targeted reports
        Route::apiResource('organisations.students', StudentController::class)->shallow();
        Route::get('students/{student_id}/created-reports', [StudentController::class, 'created-reports']);
        Route::get('students/{student_id}/targeted-reports', [StudentController::class, 'targeted-reports']);

        // Reports: list, show, store, update, delete
        Route::apiResource('organisations.reports', ReportController::class)->shallow();
    });
});