# Plan de Implementación de Sistema de Facturas

Vamos a desarrollar la aplicación del registro de inquilinos, inmuebles y facturas usando tu entorno actual (Laravel 11/12, Livewire y Flux UI component library). Esto incluye **control financiero y de impuestos trimestrales** y **claves primarias UUID** para todas las relaciones y tablas.

## Proposed Changes

### Modelos y Base de Datos (Todas las tablas usarán UUIDs)

#### [NEW] `Client` (Inquilinos y Clientes Puntuales)
Alojado en `app/Models/Client.php`.
- **Campos en Base de Datos**: `id` (UUID), `name`, `email`, `phone`, `document_number` (NIF/DNI), `billing_address`, `type` (tenant o occasional).

#### [NEW] `Property` (Inmuebles)
Alojado en `app/Models/Property.php`.
- **Campos en Base de Datos**: `id` (UUID), `name` (Nombre interno), `address` (Dirección), `monthly_rent` (Base Imponible por defecto de la renta), `default_iva_percentage` (ej: 21.00 - opcional), `default_retention_percentage` (ej: 19.00 - opcional), `is_active`.

#### [NEW] `Invoice` (Facturas)
Alojado en `app/Models/Invoice.php`.
- **Campos de Relación e Info**: `id` (UUID), `invoice_number`, `client_id` (UUID), `property_id` (UUID opcional), `issue_date` (fecha emisión), `due_date`, `status` (pending, paid), `notes`.
- **Campos Financieros y de Impuestos**:
  - `subtotal` (Base imponible: suma de las líneas sin impuestos)
  - `iva_percentage` e `iva_amount` (Porcentaje y total del IVA repercutido)
  - `retention_percentage` e `retention_amount` (Porcentaje y total de Retención/IRPF aplicada)
  - `total_amount` (Cálculo resultante final: subtotal + IVA - retenciones)

#### [NEW] `InvoiceItem` (Líneas de Factura)
Alojado en `app/Models/InvoiceItem.php`.
- **Campos en Base de Datos**: `id` (UUID), `invoice_id` (UUID), `description`, `quantity`, `unit_price`, `total` (Sin impuestos).

### Frontend y Componentes (Livewire + Flux)

Se crearán páginas y modales con un diseño muy cuidado, estilo premium, moderno e interactivo utilizando validaciones en tiempo real:

#### [NEW] Módulo de Inquilinos y Clientes
- Tabla interactiva para listar todos los clientes/inquilinos.
- Formulario tipo modal para dar de alta rápidamente a nuevos.

#### [NEW] Módulo de Inmuebles
- Configuración de la Renta Base mensual e impuestos por defecto aplicables a ese inmueble.

#### [NEW] Módulo de Facturas
- **Creador de Facturas**: Al seleccionar un alquiler recurrente, auto-rellenará los impuestos basándose en la configuración del inmueble. Permitirá calcular automáticamente el desglose de IVA y retenciones en tiempo real antes de guardar.

#### [NEW] Módulo de Informes (Impuestos Trimestrales)
- Panel donde seleccionaremos el Año y el Trimestre (T1, T2, T3, T4).
- Mostrará el volumen total facturado (Subtotal), el IVA repercutido acumulado y las Retenciones soportadas, permitiendo tener clara la liquidación trimestral.

## Verification Plan

### Manual Verification
- Carga de inquilinos, alta de un inmueble (con un 21% de IVA y 19% de IRPF).
- Generar facturas con distintos estados e impuestos aplicados.
- Comprobar que en el Informe Trimestral los cálculos (Base + IVA - IRPF) cuadran perfectamente.
