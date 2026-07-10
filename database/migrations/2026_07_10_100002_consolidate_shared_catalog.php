<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consolida el catálogo en un ÚNICO set compartido para todo el equipo.
 *
 * Hasta ahora cada usuario tenía su propio pipeline de estados y su propio catálogo de
 * servicios. Al pasar a pipeline compartido, esos duplicados por usuario aparecerían
 * repetidos en columnas del kanban y en los desplegables. Esta migración:
 *   1. Elige un usuario "principal" (primer admin; si no, el de menor id).
 *   2. Fusiona los estados duplicados por nombre: conserva el de menor id, reapunta los
 *      leads y elimina los sobrantes.
 *   3. Hace lo mismo con los servicios (reapuntando lead_service y transactions).
 *
 * Es idempotente y en una instalación con un solo usuario es prácticamente un no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        $primaryId = DB::table('users')->where('is_admin', true)->min('id')
            ?? DB::table('users')->min('id');

        if (! $primaryId) {
            return; // instalación vacía
        }

        $this->consolidate(
            table: 'lead_statuses',
            groupBy: 'name',
            remap: [['leads', 'status_id']],
            primaryId: $primaryId,
        );

        $this->consolidate(
            table: 'services',
            groupBy: 'name',
            remap: [['lead_service', 'service_id'], ['transactions', 'service_id']],
            primaryId: $primaryId,
        );
    }

    /**
     * Fusiona filas duplicadas de $table agrupando por $groupBy.
     * Conserva la de menor id, reapunta las referencias indicadas en $remap y borra el resto.
     */
    private function consolidate(string $table, string $groupBy, array $remap, int $primaryId): void
    {
        $rows = DB::table($table)->orderBy('id')->get([$groupBy, 'id']);

        $keepByKey = [];   // valor de groupBy => id conservado
        foreach ($rows as $row) {
            $key = $row->{$groupBy};
            if (! array_key_exists($key, $keepByKey)) {
                $keepByKey[$key] = $row->id; // primer id (menor) por orden ascendente
            }
        }

        foreach ($rows as $row) {
            $keepId = $keepByKey[$row->{$groupBy}];
            if ($row->id === $keepId) {
                continue;
            }

            // Reapuntar todas las referencias del duplicado al id conservado.
            foreach ($remap as [$refTable, $refColumn]) {
                DB::table($refTable)->where($refColumn, $row->id)->update([$refColumn => $keepId]);
            }

            DB::table($table)->where('id', $row->id)->delete();
        }

        // Unificar la propiedad de las filas conservadas en el usuario principal.
        DB::table($table)->whereIn('id', array_values($keepByKey))
            ->update(['user_id' => $primaryId]);
    }

    public function down(): void
    {
        // Fusión no reversible (los duplicados originales no se restauran).
    }
};
