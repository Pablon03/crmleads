# Optimización del CRM — Modelo de suscripción + equipo

Cambios para alinear el CRM con el negocio real: **suscripción mensual recurrente** y **pipeline compartido de equipo** (Pablo, Tomás, Ornella).

## Qué cambia

### 1. Recurrencia y MRR (el cambio de fondo)
- `services`: `base_price` ahora es la **cuota mensual**. Nuevos campos `is_recurring` y `setup_fee` (alta/matrícula opcional).
- `lead_service` (la suscripción de un cliente a un plan): `monthly_price` (cuota pactada), `billing_day` (día de cobro), `started_at` (alta), `canceled_at` (baja).
- Estados de la suscripción: `interested`, `proposed`, `sold` (= **Cliente activo**), `paused`, `churned` (**Baja**), `rejected`.
- **Regla clave**: al dar de baja (`churned`) **no** se borran los ingresos ya cobrados; solo se elimina la transacción automática si la venta se revierte por error a pre-venta.

### 2. Pipeline compartido de equipo
- Todo el equipo ve los mismos leads, estados, servicios y transacciones (se elimina el aislamiento por usuario).
- Nuevo campo `assigned_to` en leads: responsable operativo (filtro, columna, tarjeta de kanban y acción masiva "Asignar a").
- Catálogo de estados y servicios **unificado** (migración idempotente que fusiona duplicados por usuario).

### 3. Etapas del pipeline (reflejan tu proceso)
`Nuevo → Contactado → Demo enviada → Reunión/Diagnóstico → Propuesta enviada → Cliente activo → En riesgo → Baja → Cerrado perdido`

### 4. Los 3 planes sembrados
Presencia Local (99 €/mes), Web + SEO Local (149 €/mes), Crecimiento Local (299 €/mes).

### 5. Dashboard con métricas núcleo
MRR, Clientes activos (+ altas del mes), Ticket medio (ARPU), Bajas del mes, Leads totales, Caja del mes.

### 6. Priorización de leads
`priority_score` (0-100) calculado automáticamente: sin web (+40), pocas reseñas (+25/+12), rating (+15/+8), móvil/WhatsApp (+20/+10). Columna, filtro "Prioridad alta (70+)" y visible en el kanban.

## Cómo desplegar y validar

```bash
# 1. Migrar (crea columnas, consolida catálogo compartido y siembra planes/etapas)
php artisan migrate

# 2. Validar con la suite de tests
./vendor/bin/pest
```

> La migración `2026_07_10_100002_consolidate_shared_catalog` fusiona estados/servicios
> duplicados por usuario. Con una sola cuenta con datos es prácticamente un no-op.
> **Recomendación: haz copia de la base de datos antes de migrar en producción.**

## Ficheros nuevos
- `database/migrations/2026_07_10_100001_add_subscription_and_team_fields.php`
- `database/migrations/2026_07_10_100002_consolidate_shared_catalog.php`
- `database/migrations/2026_07_10_100003_seed_shared_pipeline_and_plans.php`
- `database/seeders/ServiceSeeder.php`
- `tests/Feature/SubscriptionTest.php`

## Pendiente (opcional, siguiente iteración)
- Comando programado `subscriptions:bill` para generar el ingreso mensual recurrente de cada cliente activo (hoy el MRR se calcula en vivo desde las suscripciones activas, no depende de un cron).
- Auto-registrar una actividad al mover un lead de etapa en el kanban.
- Campo de origen/consentimiento (RGPD) por lead para captación por email/WhatsApp.
