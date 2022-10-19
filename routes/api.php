<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Models\Attendance;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post("login", [AuthController::class, "login"]);

Route::middleware('auth:api')->group(function () {
    Route::middleware('admin')->group(function () {

        Route::get("get_users", [AuthController::class, "getUsers"]);
        Route::get("get_roles", [RoleController::class, "getRoles"]);
        Route::get("get_absents", [AttendanceController::class, "getAbsents"]);
        Route::get("get_attendances", [AttendanceController::class, "getAttendaces"]);
        
        Route::post("add_user", [AuthController::class, "addUser"]);
        Route::post("add_role", [RoleController::class, "addRole"]);
        Route::post("add_absents", [AttendanceController::class, "addAbsents"]);

        Route::put("change_status_absent", [AttendanceController::class, "changeStatusAbsent"]);
        Route::put("update_user", [AuthController::class, "updateUser"]);
        Route::put("update_role", [RoleController::class, "updateRole"]);

        Route::delete("delete_user", [AuthController::class, "deleteUser"]);
    });

    Route::post("send_attendance", [AttendanceController::class, "sendAttendance"]);

    Route::put("send_leaving", [AttendanceController::class, "sendLeaving"]);
});