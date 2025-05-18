<?php

namespace App\Http\Controllers;

use App\Models\Liquidation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class LiquidationPdfController extends Controller
{
    public function onlypdf(Liquidation $liquidation)
    {
        $liquidation->load('farm.user');

        if (!View::exists('pdfs.liquidation')) {
            abort(500, 'La vista del PDF no está disponible.');
        }

        $pdf = Pdf::setPaper('rec01')->loadView('pdfs.liquidation', [
            'liquidation' => $liquidation,
        ]);

        $filename = strtoupper($liquidation->farm->user->name . ' - ' . $liquidation->farm->name);
        $filename = str_replace(' ', '_', $filename);
        $filename = preg_replace('/[^A-Z0-9\-_]/', '', $filename);

        return $pdf->stream($filename . '.pdf');
    }
    public function allpdf(Request $request)
    {
        $ids = explode(',', $request->ids);
        $liquidations = \App\Models\Liquidation::with('farm.user')->findMany($ids);

        $pdf = Pdf::setPaper('rec01')->loadView('pdfs.liquidations_bulk', compact('liquidations'));
        // return $pdf->stream('liquidaciones.pdf');
        return $pdf->download('liquidaciones.pdf');
    }
}
