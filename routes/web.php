<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

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

Route::get("/export1", [ExportController::class, "export1"])->name("e1");
Route::get("/export2", [ExportController::class, "export2"])->name("e2");
Route::get("/export3", [ExportController::class, "export3"])->name("e3");