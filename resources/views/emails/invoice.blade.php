<!DOCTYPE html>
<html>
<head>
    <title>Factura Disponible</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #374151; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 30px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #111827; font-size: 24px; margin: 0;">{{ $settings['company_name'] ?? 'Tu Empresa' }}</h1>
    </div>

    <div style="background-color: #f9fafb; padding: 25px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin-top: 0; font-size: 18px; color: #1f2937;">Hola {{ $invoice->client->name }},</h2>
        
        @if($body)
            <p style="white-space: pre-wrap;">{!! nl2br(e($body)) !!}</p>
        @else
            <p>Adjuntamos la factura <strong>{{ $invoice->invoice_number }}</strong> correspondiente a su último periodo, por un importe total de <strong>€{{ number_format($invoice->total_amount, 2) }}</strong>.</p>
            <p>Por favor, revisa el documento PDF adjunto en este mensaje con todos los conceptos desglosados.</p>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 14px; color: #6b7280;">
            <p style="margin: 0;">Si tienes alguna pregunta sobre los importes, no dudes en responder directamente a este correo.</p>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #9ca3af;">
        <p>Este es un mensaje automático. Protege el medio ambiente, no imprimas este documento si no es necesario.</p>
    </div>
</body>
</html>
