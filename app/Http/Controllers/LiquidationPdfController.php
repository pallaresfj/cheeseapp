<?php

namespace App\Http\Controllers;

use App\Models\Liquidation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class LiquidationPdfController extends Controller
{
    public function show(Liquidation $liquidation)
    {
        $liquidation->load('farm.user');

        if (!View::exists('pdfs.liquidation')) {
            abort(500, 'La vista del PDF no está disponible.');
        }

        $pdf = Pdf::loadView('pdfs.liquidation', [
            'liquidation' => $liquidation,
        ]);

        return $pdf->stream('Liquidacion_' . $liquidation->id . '.pdf');
    }
}
