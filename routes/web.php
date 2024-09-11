<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::post('upload',[VideoController::class,'upload'])->name('video.upload');
Route::get('videos/{id}', [VideoController::class, 'show'])->name('video.show');
