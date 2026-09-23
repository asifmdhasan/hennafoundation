<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ProjectsController;

Route::get('/', HomeController::class)->name('home');
Route::get('/about-us/', AboutUsController::class)->name('about-us');
Route::get('/projects/', ProjectsController::class)->name('projects.index');
Route::get('/blog/', BlogController::class)->name('blog.index');
