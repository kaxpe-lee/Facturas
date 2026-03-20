# Resumen del Proyecto: Sistema de Facturas y Alquileres

Hemos completado exitosamente la creación del sistema web premium para el registro de inquilinos, inmuebles, facturas e impuestos trimestrales utilizando tu stack actual de **Laravel + Livewire 3 + Flux UI**.

## Cambios Implementados

1. **Base de Datos y Modelos (UUIDs)**
   - Modelos creados: `Client`, `Property`, `Invoice`, y `InvoiceItem`.
   - Todas las migraciones usan `uuid` como clave primaria en todas las tablas, garantizando IDs únicas y robustas.
   - Las relaciones se han implementado mediante `foreignUuid` y cascadas de borrado lógicas.

2. **Módulo de Clientes (Inquilinos y Puntuales)**
   - Formulario de alta/edición de clientes integrado de forma impecable usando la UI de `Flux::modal` junto a clases `Livewire/Form`.
   - La tabla dispone de buscador en tiempo real.

3. **Módulo de Inmuebles**
   - Panel de control de propiedades.
   - Asignación de inquilinos recurrentes y registro de la cuota mensual de alquiler y sus impuestos por defecto (IVA % y Retención IRPF %).

4. **Constructur de Facturas Reactivo (Invoice Builder)**
   - Página interactiva para "Nueva Factura" que, al seleccionar el cliente y el inmueble vinculado, autocompleta la información base.
   - Sistema dinámico de adición y eliminación de conceptos/líneas de la factura.
   - **Cálculo en tiempo real**: Suma automáticamente la Base Imponible, el IVA repercutido y alerta de las retenciones en milisegundos gracias a las nuevas propiedades `#[Computed]` nativas de Livewire.

5. **Informe Financiero y de Impuestos**
   - Módulo de informes que agrupa la facturación por Trimestres del año empleando métodos potentes nativos (ej: `whereBetween` y librerías temporales como `Carbon`).
   - Muestra el volumen total (Base), el IVA y la Retención (IRPF) para facilitar el papeleo con la Agencia Tributaria.
   - Incluye interactividad de tabla detallada por trimestre para revisión de desglose.

6. **Navegación Intuitiva**
   - Todos los accesos a estos nuevos módulos figuran ya en el Panel Lateral (Sidebar).

7. **Exportación a PDF**
   - Impresión física de Facturas e Informes Fiscales (vía librería `dompdf`), con formato factura A4.

8. **Configuración Dinámica de Emisor**
   - Panel de "Configuración Emisor" inyectado en Base de Datos (UUID).
   - Permite alterar el Nombre y Datos de Facturación de la empresa auto-rellenándolos dinámicamente en los PDF al instante.

## Verificación Local

Todas las validaciones, bases y tablas han sido inyectadas a PostgreSQL mediante los comandos de `artisan migrate` tras resolver la conexión `.env`.

Al ser un proyecto provisto bajo **Laravel Herd**, simplemente entra a tu aplicación web (ej. `http://facturas.test` o tu dominio actual de Herd), inicia sesión si estabas detrás de una pantalla de login y prueba la experiencia completa.
