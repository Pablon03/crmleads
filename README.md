# CRM Leads

CRM personal para gestión de leads de ventas a puerta fría, con importación desde Google Maps, kanban personalizable y contabilidad básica.

---

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.5+ |
| Laravel | 12.x |
| Filament | 5.x — panel en `/admin` |
| Base de datos | Supabase (Postgres) |
| Queue | Laravel Queue — driver `database` |
| Kanban | relaticle/flowforge 4.x |
| Tests | Pest 3.x |

---

## Instalación

### 1. Clonar y dependencias

```bash
git clone <repo> crm-leads
cd crm-leads
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configurar Supabase

En tu [dashboard de Supabase](https://supabase.com/dashboard) → **Settings → Database → Connection string**, usa los datos del **Session mode** (puerto 5432):

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-eu-west-1.pooler.supabase.com   # Session mode host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.XXXXXXXXXXXXXXXX           # Incluye el project ref
DB_PASSWORD=tu_password
DB_SSLMODE=require
```

> Usa el **Session mode** (puerto 5432) en lugar del Transaction mode. Laravel necesita prepared statements y transacciones que el Transaction mode no soporta.

### 3. Migraciones y datos iniciales

```bash
php artisan migrate
php artisan db:seed     # Crea los 6 estados kanban por defecto
```

Tras migrar, desactiva RLS en Supabase (Laravel gestiona autorización con Policies y Global Scopes):

```sql
-- Ejecutar en el SQL Editor de tu proyecto Supabase
ALTER TABLE folders          DISABLE ROW LEVEL SECURITY;
ALTER TABLE lead_statuses    DISABLE ROW LEVEL SECURITY;
ALTER TABLE services         DISABLE ROW LEVEL SECURITY;
ALTER TABLE leads            DISABLE ROW LEVEL SECURITY;
ALTER TABLE lead_service     DISABLE ROW LEVEL SECURITY;
ALTER TABLE activities       DISABLE ROW LEVEL SECURITY;
ALTER TABLE transactions     DISABLE ROW LEVEL SECURITY;
```

### 4. Crear usuario administrador

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name'              => 'Pablo Nicolás',
    'email'             => 'pablomarbor03@gmail.com',
    'password'          => bcrypt('Pablon1970@2003'),
    'email_verified_at' => now(),
]);
```

Los 6 estados kanban se crean automáticamente para cada usuario nuevo.

### 5. Iniciar

```bash
# Terminal 1 — Servidor web
php artisan serve

# Terminal 2 — Worker de colas (para importación)
php artisan queue:work --tries=3
```

Accede a **http://localhost:8000/admin**

---

## Variables de entorno relevantes

```env
APP_LOCALE=es
APP_TIMEZONE=Europe/Madrid

QUEUE_CONNECTION=database

# Driver de scraping (ver sección más abajo)
LEAD_SCRAPER_DRIVER=dummy
# LEAD_SCRAPER_API_KEY=
```

---

## Módulos del panel

| Ruta | Descripción |
|---|---|
| `/admin` | Dashboard — estadísticas, gráficos, follow-ups, actividad reciente |
| `/admin/leads` | Leads con filtros, búsqueda y acción de importar desde Google Maps |
| `/admin/lead-kanban-page` | Vista Kanban con drag-and-drop entre estados |
| `/admin/folders` | Carpetas para organizar leads |
| `/admin/lead-statuses` | Estados del kanban (reordenables) |
| `/admin/services` | Servicios que ofreces |
| `/admin/transactions` | Contabilidad de ingresos y gastos |

---

## Importación de leads

### DummyDriver (incluido, sin configuración)

Desde el panel → **Leads → Importar desde Google Maps**. El `DummyDriver` devuelve 5 negocios fake en Madrid para probar el flujo completo sin API real.

### Añadir un driver real

La arquitectura usa el patrón Adapter. Para conectar una API real solo necesitas 3 pasos:

#### Paso 1 — Crear el driver

```php
// app/Services/LeadScraping/Drivers/SerpApiDriver.php

namespace App\Services\LeadScraping\Drivers;

use App\Services\LeadScraping\LeadScraperInterface;
use App\Services\LeadScraping\ScrapedBusiness;
use Illuminate\Support\Facades\Http;

class SerpApiDriver implements LeadScraperInterface
{
    public function __construct(private string $apiKey) {}

    public function fetchBusinesses(
        string $query,
        string $location,
        int $radiusMeters = 5000,
        int $limit = 20,
    ): array {
        $response = Http::get('https://serpapi.com/search.json', [
            'engine'  => 'google_maps',
            'q'       => "{$query} en {$location}",
            'api_key' => $this->apiKey,
            'num'     => $limit,
        ]);

        return collect($response->json('local_results', []))
            ->map(fn ($r) => new ScrapedBusiness(
                business_name:   $r['title'],
                address:         $r['address'] ?? null,
                phone:           $r['phone'] ?? null,
                website:         $r['website'] ?? null,
                category:        $r['type'] ?? null,
                rating:          $r['rating'] ?? null,
                reviews_count:   $r['reviews'] ?? null,
                google_place_id: $r['place_id'] ?? null,
                latitude:        $r['gps_coordinates']['latitude'] ?? null,
                longitude:       $r['gps_coordinates']['longitude'] ?? null,
                raw_data:        $r,
            ))
            ->all();
    }
}
```

#### Paso 2 — Registrar en el ServiceProvider

```php
// app/Providers/LeadScrapingServiceProvider.php — método register()
return match ($driver) {
    'dummy'   => new DummyDriver(),
    'serpapi' => new SerpApiDriver(config('lead_scraping.api_key')),
};
```

#### Paso 3 — Activar en `.env`

```env
LEAD_SCRAPER_DRIVER=serpapi
LEAD_SCRAPER_API_KEY=tu_api_key_aqui
```

### APIs recomendadas

| API | Coste aprox. | Notas |
|---|---|---|
| [SerpApi](https://serpapi.com) | ~$50/mes (5k búsquedas) | Google Maps Results API, fácil de integrar |
| [Outscraper](https://outscraper.com) | Pay-per-use desde $3/1000 | Especializado en Google Maps, muy completo |
| [Apify — Google Maps Scraper](https://apify.com/compass/crawler-google-places) | Pay-per-use | Actor activamente mantenido |
| [Google Places API](https://developers.google.com/maps/documentation/places) | $17/1000 requests | Oficial, requiere facturación en GCP |

---

## Arquitectura de datos

```
users
  ├── folders          (carpetas para organizar leads)
  ├── lead_statuses    (columnas del kanban, personalizables por usuario)
  ├── services         (servicios que ofreces)
  ├── leads            (negocios importados / creados manualmente)
  │     ├── lead_service   (pivot: qué servicios tiene cada lead y en qué fase)
  │     ├── activities     (historial: llamadas, emails, reuniones, etc.)
  │     └── transactions   (ingresos vinculados a ventas)
  └── transactions     (contabilidad general: ingresos y gastos)
```

**Multi-tenant por diseño**: todas las tablas llevan `user_id` con FK cascade. El trait `HasUserScope` aplica automáticamente `WHERE user_id = auth()->id()` a todas las queries. Preparado para escalar a múltiples usuarios sin cambios estructurales.

### Automatizaciones

- **Al marcar un servicio como `sold`**: el `LeadServiceObserver` crea automáticamente una `Transaction` de tipo `income` con el precio de venta (o el precio base del servicio si no se especificó).
- **Al revertir de `sold` a otro estado**: la transaction automática se elimina.

---

## Tests

Los tests usan **SQLite en memoria** y no tocan Supabase.

```bash
./vendor/bin/pest                        # Todos los tests (8 tests, 21 assertions)
./vendor/bin/pest --filter "import"      # Solo tests de importación
```

Cobertura:
- Creación de lead y asignación de `user_id`
- Global Scope: cada usuario solo ve sus propios leads
- Scope `withFollowUpDue`: filtra solo los follow-ups vencidos
- Importación con DummyDriver y deduplicación por `google_place_id`
- Asignación del estado por defecto al importar
- Observer: creación automática de `Transaction` al marcar `sold`
- Observer: eliminación de `Transaction` al revertir estado
- Observer: uso del precio base cuando no hay `sold_price`

---

## Queue worker en producción

```bash
# Con Supervisor (recomendado)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

# Monitorizar jobs fallidos
php artisan queue:failed
php artisan queue:retry all
```
