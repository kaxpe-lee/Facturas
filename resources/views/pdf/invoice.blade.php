<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; }
        .invoice-box { width: 100%; max-width: 800px; margin: auto; padding: 20px; box-sizing: border-box; }
        .header { width: 100%; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .header .title { font-size: 36px; font-weight: bold; color: #333; }
        .header .details { text-align: right; line-height: 1.6; }
        
        .addresses { width: 100%; margin-bottom: 40px; }
        .addresses table { width: 100%; border-collapse: collapse; }
        .addresses td { vertical-align: top; width: 50%; }
        .addresses h3 { margin-top: 0; color: #666; font-size: 14px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table-items th { background: #eee; padding: 10px; border: 1px solid #ddd; text-align: left; }
        .table-items td { padding: 10px; border: 1px solid #ddd; }
        .table-items th.right, .table-items td.right { text-align: right; }
        .table-items th.center, .table-items td.center { text-align: center; }
        
        .totals { width: 50%; float: right; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 8px; border-bottom: 1px solid #eee; }
        .totals td.label { font-weight: bold; text-align: left; }
        .totals td.value { text-align: right; }
        .totals td.total-label { font-weight: bold; font-size: 18px; background: #eee; }
        .totals td.total-value { font-weight: bold; font-size: 18px; background: #f9f9f9; text-align: right; }
        
        .notes { clear: both; margin-top: 60px; padding-top: 20px; border-top: 1px solid #eee; font-size: 11px; color: #777; line-height: 1.5; }
        
        .status { padding: 4px 8px; display: inline-block; color: white; font-weight: bold; border-radius: 4px; font-size: 14px; }
        .status.paid { background-color: #28a745; }
        .status.pending { background-color: #ffc107; color: #333; }
        
        .clearfix { clear: both; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <table>
                <tr>
                    <td>
                        <span class="title">FACTURA</span><br>
                        Nº {{ $invoice->invoice_number }}
                        @if($invoice->status == 'paid')
                            <br><br><span style="color: #28a745; font-weight:bold; font-size:16px;">PAGADA</span>
                        @else
                            <br><br><span style="color: #ffc107; font-weight:bold; font-size:16px;">PENDIENTE</span>
                        @endif
                    </td>
                    <td class="details">
                        <strong>Fecha Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                        @if($invoice->due_date)
                        <strong>Fecha Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                        @endif
                        @if($invoice->property)
                        <strong>Inmueble:</strong> {{ $invoice->property->name }}<br>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="addresses">
            <table>
                <tr>
                    <td style="padding-right: 20px;">
                        <h3>Emisor</h3>
                        <strong>{{ \App\Models\Setting::where('key', 'company_name')->value('value') ?: config('app.name') }}</strong><br>
                        {!! nl2br(e(\App\Models\Setting::where('key', 'company_address')->value('value') ?: 'Dirección Fiscal del Emisor')) !!}<br>
                        NIF: {{ \App\Models\Setting::where('key', 'company_nif')->value('value') ?: 'B00000000' }}<br>
                        {{ \App\Models\Setting::where('key', 'company_email')->value('value') ?: 'info@tudominio.com' }}<br>
                        {{ \App\Models\Setting::where('key', 'company_phone')->value('value') }}
                    </td>
                    <td>
                        <h3>Facturar a</h3>
                        <strong>{{ $invoice->client->name }}</strong><br>
                        @if($invoice->client->document_number)
                            NIF/DNI: {{ $invoice->client->document_number }}<br>
                        @endif
                        @if($invoice->client->email)
                            Email: {{ $invoice->client->email }}<br>
                        @endif
                        @if($invoice->client->phone)
                            Tel: {{ $invoice->client->phone }}<br>
                        @endif
                        @if($invoice->client->billing_address)
                            <br>{{ nl2br(e($invoice->client->billing_address)) }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="table-items">
            <thead>
                <tr>
                    <th>Concepto / Descripción</th>
                    <th class="center" style="width: 10%;">Cant.</th>
                    <th class="right" style="width: 15%;">Precio Ud.</th>
                    <th class="right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">€{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">€{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Base Imponible:</td>
                    <td class="value">€{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->iva_amount > 0)
                <tr>
                    <td class="label">IVA ({{ number_format($invoice->iva_percentage, 2) }}%):</td>
                    <td class="value">€{{ number_format($invoice->iva_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->retention_amount > 0)
                <tr>
                    <td class="label">Retención IRPF ({{ number_format($invoice->retention_percentage, 2) }}%):</td>
                    <td class="value text-red-500">-€{{ number_format($invoice->retention_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="total-label">Total a Pagar:</td>
                    <td class="total-value">€{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        @if($invoice->notes)
        <div class="notes">
            <strong>Notas Adicionales:</strong><br>
            {!! nl2br(e($invoice->notes)) !!}
        </div>
        @endif
        
    </div>
</body>
</html>
