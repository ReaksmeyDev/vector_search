<?php

use App\Http\Controllers\RuleSearchController;
use Illuminate\Support\Facades\Route;

// Rule Vector Search Engine Interactive Demo
Route::get('/', [RuleSearchController::class, 'index'])->name('rules.index');
Route::get('/demo', [RuleSearchController::class, 'index'])->name('rules.demo');

// Store New Legal Rule Entry
Route::post('/rules', [RuleSearchController::class, 'store'])->name('rules.store');
