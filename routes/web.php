<?php

use App\Http\Controllers\CandidateAnalysisController;
use App\Http\Controllers\JobOfferController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('offers', JobOfferController::class);

    Route::get('/offers/{offer}/candidates/create', [CandidateAnalysisController::class, 'create'])
        ->name('candidates.create');
    Route::post('/offers/{offer}/candidates', [CandidateAnalysisController::class, 'store'])
        ->name('candidates.store');
    Route::get('/analyses/{analysis}', [CandidateAnalysisController::class, 'show'])
        ->name('analyses.show');
});

require __DIR__.'/auth.php';
