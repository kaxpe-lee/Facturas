<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumen Trimestral {{ $quarter }}T {{ $year }}</title>
    <style>
        body { font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .invoice-box { width: 100%; margin: auto; padding: 20px; box-sizing: border-box; }
        .header { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .header .title { font-size: 28px; font-weight: bold; color: #333; text-transform: uppercase; }
        .header .details { text-align: right; line-height: 1.6; }
        
        .summary { width: 100%; margin-bottom: 30px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary th { background: #333; color: white; padding: 10px; text-align: center; }
        .summary td { border: 1px solid #ddd; padding: 15px; text-align: center; font-size: 18px; font-weight: bold; }
        .iva-box { color: #0056b3; }
        .irpf-box { color: #dc3545; }

        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table-items th { background: #eee; padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 10px; text-transform: uppercase; }
        .table-items td { padding: 6px 8px; border: 1px solid #ddd; }
        .table-items th.right, .table-items td.right { text-align: right; }
        .table-items th.center, .table-items td.center { text-align: center; }
        
        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 5px;}
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <table>
                <tr>
                    <td>
                        <span class="title">INFORME FISCAL TRIMESTRAL</span><br>
                        PERIODO: <strong>Trimestre {{ $quarter }} - Año {{ $year }}</strong>
                    </td>
                    <td class="details">
                        <strong>EMITIDO EL:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}<br>
                        <strong>TOTAL FACTURAS:</strong> {{ $data['count'] }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="summary">
            <table>
                <tr>
                    <th>BASE IMPONIBLE (INGRESOS Brutos)</th>
                    <th>IVA REPERCUTIDO (A pagar a Hacienda)</th>
                    <th>RETENCIONES IRPF (A deducir)</th>
                    <th>TOTAL INGRESADO (Neto cobrado)</th>
                </tr>
                <tr>
                    <td>€{{ number_format($data['base'], 2) }}</td>
                    <td class="iva-box">€{{ number_format($data['iva'], 2) }}</td>
                    <td class="irpf-box">-€{{ number_format($data['retention'], 2) }}</td>
                    <td>€{{ number_format($data['total'], 2) }}</td>
                </tr>
            </table>
        </div>

        <h3 style="color: #666; font-size: 14px; margin-bottom: 5px;">DESGLOSE POR FACTURAS</h3>
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width: 10%;">Número</th>
                    <th class="center" style="width: 10%;">Fecha Emisión</th>
                    <th style="width: 30%;">Cliente</th>
                    <th class="right" style="width: 12%;">Base Imp.</th>
                    <th class="right" style="width: 12%;">IVA</th>
                    <th class="right" style="width: 12%;">IRPF</th>
                    <th class="right" style="width: 14%;">Total Factura</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['invoices'] as $invoice)
                <tr>
                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                    <td class="center">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                    <td>
                        {{ $invoice->client->name }}<br>
                        <span style="font-size: 9px; color: #777;">{{ $invoice->client->document_number }}</span>
                    </td>
                    <td class="right">€{{ number_format($invoice->subtotal, 2) }}</td>
                    <td class="right" style="color: #0056b3;">€{{ number_format($invoice->iva_amount, 2) }}</td>
                    <td class="right" style="color: #dc3545;">-€{{ number_format($invoice->retention_amount, 2) }}</td>
                    <td class="right" style="font-weight: bold;">€{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="center" style="padding: 20px;">No hay facturas emitidas en este trimestre.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="right">TOTALES DEL TRIMESTRE:</th>
                    <th class="right">€{{ number_format($data['base'], 2) }}</th>
                    <th class="right">€{{ number_format($data['iva'], 2) }}</th>
                    <th class="right">-€{{ number_format($data['retention'], 2) }}</th>
                    <th class="right">€{{ number_format($data['total'], 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <!--
        <div class="footer">
            Generado automáticamente por tu Sistema de Facturación
        </div>
        -->
    </div>
</body>
</html>
