<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\VolunteerApplicationController;
use App\Http\Controllers\PartnershipApplicationController;

Route::get('/', HomeController::class)->name('home');
Route::get('/about-us/', AboutUsController::class)->name('about-us');
Route::get('/projects/', ProjectsController::class)->name('projects.index');
Route::get('/blog/', BlogController::class)->name('blog.index');
Route::get('/contact-us/', [ContactMessageController::class, 'create'])->name('contact-us.create');
Route::post('/contact-us/', [ContactMessageController::class, 'store'])->middleware('throttle:contact-submissions')->name('contact-us.store');
Route::get('/volunteer-registration/', [VolunteerApplicationController::class, 'create'])->name('volunteer-registration.create');
Route::post('/volunteer-registration/', [VolunteerApplicationController::class, 'store'])->middleware('throttle:volunteer-applications')->name('volunteer-registration.store');
Route::get('/partnership-registration/', [PartnershipApplicationController::class, 'create'])->name('partnership-registration.create');
Route::post('/partnership-registration/', [PartnershipApplicationController::class, 'store'])->middleware('throttle:partnership-applications')->name('partnership-registration.store');
