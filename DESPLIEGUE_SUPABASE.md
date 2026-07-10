# Despliegue seguro en Railway + Supabase

Las migraciones se ejecutan **solas** al desplegar (`start.sh` → `php artisan migrate --force`).
Ahora, si una migración falla, el arranque **se detiene** (antes seguía con el esquema a medias).

## Paso 1 — Copia de seguridad de Supabase (obligatorio)

Opción A (rápida): *Supabase Dashboard → Database → Backups → crear snapshot*.

Opción B (`pg_dump`, copia local descargable). Coge la cadena de conexión en
*Supabase → Project Settings → Database → Connection string (URI)* y:

```bash
pg_dump "postgresql://postgres:[PASSWORD]@[HOST]:5432/postgres" \
  --no-owner --no-privileges -Fc -f backup_crm_$(date +%Y%m%d).dump
```

Restaurar (si hiciera falta volver atrás):

```bash
pg_restore --clean --no-owner --no-privileges \
  -d "postgresql://postgres:[PASSWORD]@[HOST]:5432/postgres" backup_crm_YYYYMMDD.dump
```

## Paso 2 — Desplegar

```bash
git add -A
git commit -m "CRM: modelo de suscripción mensual + pipeline compartido de equipo"
git push
```

Railway reconstruye y migra al arrancar.

## Paso 3 — Verificar el deploy (no te fíes solo del build en verde)

En los **logs de Railway** busca `==> Running migrations...` y que **no** aparezca
`Migración fallida`. Luego, en el **SQL Editor de Supabase**, comprueba que todo aplicó:

```sql
-- Campos de suscripción
select column_name from information_schema.columns
where table_name = 'lead_service'
  and column_name in ('monthly_price','billing_day','started_at','canceled_at');

-- Campos de equipo/priorización
select column_name from information_schema.columns
where table_name = 'leads'
  and column_name in ('assigned_to','priority_score','source');

-- Los 3 planes sembrados
select name, base_price, is_recurring from services order by base_price;

-- Etapas del pipeline (una sola lista compartida)
select name, position, is_default from lead_statuses order by position;
```

Deberías ver los 4 + 3 campos, los 3 planes (99/149/299) y las 9 etapas.
En el CRM: el dashboard muestra **MRR** y la tabla de leads la columna **Prioridad**.

## Si algo falla
El arranque se detendrá y lo verás en los logs. Restaura el backup del Paso 1,
avísame con el error del log y lo corrijo antes de reintentar.
