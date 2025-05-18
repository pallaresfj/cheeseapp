<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $liquidation->farm->user->name }} - {{ $liquidation->farm->name }}</title>
    <style>
        @page {
            margin: 6mm;
        }

        body {
            font-family: Helvetica, sans-serif;
            font-size: 9px;
        }
        .footer {
            position: fixed;
            bottom: 6mm;
            left: 0;
            right: 0;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin: 0;">
            <thead>
                <tr>
                    <th align="center" valign="middle" width="25%">
                        <img src="images/{{ $settings['empresa.logo'] }}" width="70">
                    </th>
                    <th align="center" valign="middle" width="75%" style="font-weight: bold; font-size: 13px;">{{ $settings['empresa.nombre'] }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" align="center" style="padding-top: 6px;">NIT: {{ $settings['empresa.nit'] }}</td>
                </tr>
                <tr>
                    <td colspan="2" align="center">{{ $settings['empresa.direccion'] }}.<br>{{ $settings['empresa.telefono'] }}</td>
                </tr>
                <tr>
                    <td colspan="2" align="center"></td>
                </tr>
                <tr>
                    <td colspan="2" align="right" style="padding-top: 6px; font-size: 11px;">Factura # <b>{{ str_pad($liquidation->id, 6, '0', STR_PAD_LEFT) }}</b> del <b>
                        @php
                            $fecha = $liquidation->date instanceof \Carbon\Carbon
                                ? $liquidation->date
                                : \Carbon\Carbon::parse($liquidation->date);
                        @endphp
                            {{ $fecha->format('d/m/Y') }}
                        </b>
                    </td>
                </tr>
                <tr>
                    <td align="center"></td>
                </tr>
                <tr>
                    <td colspan="2" align="left" style="padding-top: 6px;">
                        Proveedor<br>
                        <span style="font-weight: bold; font-size: 12px; text-transform: uppercase;">
                            {{ $liquidation->farm->user->name }} - {{ $liquidation->farm->name }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @php
        $detalles = is_string($liquidation->details)
            ? json_decode($liquidation->details, true)
            : $liquidation->details;
    @endphp

    @if (!empty($detalles))
        <div style="text-align: center; margin-top: 10px; font-weight: bold; font-size: 11px;">Producción Recibida</div>
        <table border="0" cellpadding="2" cellspacing="0" width="100%" style="margin-top: 5px;">
            <thead>
                <tr>
                    <th colspan="2" style="text-align: center;">Fecha</th>
                    <th style="text-align: right;">Litros</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                    <tr>
                        <td>{{ ucfirst(\Carbon\Carbon::parse($detalle['date'])->locale('es')->isoFormat('dddd')) }}</td>
                        <td>{{ \Carbon\Carbon::parse($detalle['date'])->format('d/m/Y') }}</td>
                        <td style="text-align: right;">{{ number_format($detalle['liters'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="text-align: center;">
        <table border="0" cellpadding="0" cellspacing="0" style="margin:auto;" width="100%">
            <tbody>
                <tr>
                    <td style="font-weight: bold; text-align:right; padding-top: 12px;">Total Litros:</td>
                    <td style="font-weight: bold; text-align:right; padding-top: 12px;">{{ number_format($liquidation->total_liters, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; text-align:right;">Valor Litro:</td>
                    <td style="font-weight: bold; text-align:right;">${{ number_format($liquidation->price_per_liter, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: left; font-weight: bold; padding-top: 12px;">Total Ingresos:</td>
                    <td style="text-align: right; font-weight: bold; padding-top: 12px;">${{ number_format($liquidation->total_liters * $liquidation->price_per_liter, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">-Prestamos</td>
                    <td style="text-align: right;">${{ number_format($liquidation->loan_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">-Saldo anterior</td>
                    <td style="text-align: right;">${{ number_format($liquidation->previous_balance, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">-Descuento préstamo</td>
                    <td style="text-align: right;">${{ number_format($liquidation->discounts, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align:left;">-Saldo préstamo</td>
                    <td style="text-align:right;">${{ number_format($liquidation->previous_balance - $liquidation->discounts, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; text-align:left; padding-top: 12px;">Neto a Pagar</td>
                    <td style="font-weight: bold; text-align:right; padding-top: 12px;">${{ number_format(($liquidation->total_liters * $liquidation->price_per_liter) - $liquidation->discounts, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="text-align: center;">
        <table border="0" cellpadding="0" cellspacing="0" style="margin:auto;">
            <tbody>
                <tr>
                    <td style="font-weight: bold; text-align:center; padding-top: 12px;">Concepto Préstamo</td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-top: 6px;">
                        {!! nl2br(e(
                            preg_replace('/^\h+/m', '', trim(
                                \App\Models\Loan::where('user_id', $liquidation->farm->user_id)
                                    ->whereIn('status', ['active', 'suspended', 'overdue'])
                                    ->value('description') ?? '---'
                            ))
                        )) !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">www.cheeseapp.com.co</div>


</body>
</html>