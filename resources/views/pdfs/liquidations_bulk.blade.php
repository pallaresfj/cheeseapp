<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0 1cm; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @foreach ($liquidations as $loopIndex => $liquidation)
        <div class="page" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
            <h2>Liquidación: {{ $liquidation->farm->user->name }} - {{ $liquidation->farm->name }}</h2>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($liquidation->date)->format('d/m/Y') }}</p>
            <table>
                <tr><th>Campo</th><th>Valor</th></tr>
                <tr><td>Litros</td><td>{{ number_format($liquidation->total_liters, 2, ',', '.') }}</td></tr>
                <tr><td>Precio</td><td>${{ number_format($liquidation->price_per_liter, 0, ',', '.') }}</td></tr>
                <tr><td>Producido</td><td>${{ number_format($liquidation->total_liters * $liquidation->price_per_liter, 0, ',', '.') }}</td></tr>
                <tr><td>Prestado</td><td>${{ number_format($liquidation->loan_amount, 0, ',', '.') }}</td></tr>
                <tr><td>Debe</td><td>${{ number_format($liquidation->previous_balance, 0, ',', '.') }}</td></tr>
                <tr><td>Descuentos</td><td>${{ number_format($liquidation->discounts, 0, ',', '.') }}</td></tr>
                <tr><td>Nuevo Saldo</td><td>${{ number_format($liquidation->previous_balance - $liquidation->discounts, 0, ',', '.') }}</td></tr>
                <tr><td>Neto</td><td>${{ number_format(($liquidation->total_liters * $liquidation->price_per_liter) - $liquidation->discounts, 0, ',', '.') }}</td></tr>
            </table>
        </div>
    @endforeach
</body>
</html>