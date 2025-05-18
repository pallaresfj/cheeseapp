<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiquidationPdfController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/liquidation/{liquidation}/pdf', [LiquidationPdfController::class, 'onlypdf'])
    ->name('filament.liquidation.pdf');

Route::get('/admin/liquidations/pdf/bulk', [LiquidationPdfController::class, 'allpdf'])
    ->name('filament.liquidations.bulk-pdf');
