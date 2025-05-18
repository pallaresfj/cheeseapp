<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiquidationPdfController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/liquidation/{liquidation}/pdf', [LiquidationPdfController::class, 'show'])
    ->name('filament.liquidation.pdf');

Route::get('/admin/liquidations/pdf/bulk', function (Request $request) {
    $ids = explode(',', $request->ids);
    $liquidations = \App\Models\Liquidation::with('farm.user')->findMany($ids);

    $pdf = Pdf::loadView('pdfs.liquidations_bulk', compact('liquidations'));

    return $pdf->stream('liquidaciones.pdf');
})->name('filament.liquidations.bulk-pdf');
