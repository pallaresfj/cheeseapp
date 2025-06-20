<?php

namespace App\Http\Controllers;

use App\Models\Liquidation;
use App\Models\Setting;
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

        // Obtener medidas desde configuración
        $width = Setting::where('key', 'sistema.paper_width_mm')->value('value') * 2.8346;
        $height = Setting::where('key', 'sistema.paper_height_mm')->value('value') * 2.8346;
        dd($width, $height);
        // Configurar el tamaño del papel
        $pdf = Pdf::setPaper([0, 0, $width, $height])
            ->loadView('pdfs.liquidation', [
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

        // Obtener medidas desde configuración
        $width = Setting::where('key', 'sistema.paper_width_mm')->value('value') * 2.8346;
        $height = Setting::where('key', 'sistema.paper_height_mm')->value('value') * 2.8346;

        $pdf = Pdf::setPaper([0, 0, $width, $height])
            ->loadView('pdfs.liquidations_bulk', compact('liquidations'));
        return $pdf->download('liquidaciones.pdf');
    }
}
