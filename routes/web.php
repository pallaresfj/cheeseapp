<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiquidationPdfController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('home');
})->name('home');
Route::get('/about', function () {
    return view('about');
});
Route::get('/contact', function () {
    return view('contact');
});

Route::get('/admin/liquidation/{liquidation}/pdf', [LiquidationPdfController::class, 'onlypdf'])
    ->name('filament.liquidation.pdf');

Route::get('/admin/liquidations/pdf/bulk', [LiquidationPdfController::class, 'allpdf'])
    ->name('filament.liquidations.bulk-pdf');
