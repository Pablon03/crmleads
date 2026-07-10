<?php

namespace App\Observers;

use App\Models\LeadService;
use App\Models\Transaction;

/**
 * Mantiene sincronizada la contabilidad con el ciclo de vida de la suscripción.
 *
 * Estados relevantes del pivot lead_service:
 *   interested / proposed   → pre-venta (aún no factura)
 *   sold                    → CLIENTE ACTIVO (suscripción viva, cuenta para MRR)
 *   paused                  → pausada (no factura, pero NO es baja)
 *   churned                 → BAJA real (dejó de ser cliente)
 *   rejected                → propuesta rechazada (nunca llegó a cliente)
 *
 * Regla clave de suscripción: al dar de baja (churned) NO se borran los ingresos ya
 * registrados: ese dinero se cobró de verdad. Solo se elimina la transacción automática
 * cuando la venta se revierte por error a un estado de pre-venta.
 */
class LeadServiceObserver
{
    /** Estados de pre-venta: revertir a uno de ellos significa "fue un error". */
    private const PRESALE = ['interested', 'proposed', 'rejected'];

    public function updated(LeadService $leadService): void
    {
        if (! $leadService->wasChanged('status')) {
            return;
        }

        $newStatus = $leadService->status;
        $oldStatus = $leadService->getOriginal('status');

        // Pasó a "cliente activo" → alta + primer ingreso.
        if ($newStatus === 'sold' && $oldStatus !== 'sold') {
            if (blank($leadService->started_at)) {
                $leadService->updateQuietly(['started_at' => now()]);
            }
            $this->createIncomeTransaction($leadService);
            return;
        }

        // Dejó de ser cliente activo.
        if ($oldStatus === 'sold' && $newStatus !== 'sold') {
            // Baja real → registrar fecha de baja, conservar ingresos históricos.
            if ($newStatus === 'churned' && blank($leadService->canceled_at)) {
                $leadService->updateQuietly(['canceled_at' => now()]);
            }

            // Reversión por error a pre-venta → borrar la transacción automática.
            if (in_array($newStatus, self::PRESALE, true)) {
                Transaction::withoutGlobalScopes()
                    ->where('lead_service_id', $leadService->id)
                    ->where('type', 'income')
                    ->delete();
            }
        }
    }

    public function created(LeadService $leadService): void
    {
        if ($leadService->status === 'sold') {
            if (blank($leadService->started_at)) {
                $leadService->updateQuietly(['started_at' => now()]);
            }
            $this->createIncomeTransaction($leadService);
        }
    }

    private function createIncomeTransaction(LeadService $leadService): void
    {
        $lead = $leadService->lead()->withoutGlobalScopes()->first();
        if (! $lead) {
            return;
        }

        $service  = $leadService->service()->withoutGlobalScopes()->first();
        $amount   = $leadService->monthlyValue();
        $soldDate = $leadService->sold_at ?? $leadService->started_at ?? now();

        Transaction::create([
            'user_id'         => $lead->user_id,
            'lead_id'         => $leadService->lead_id,
            'service_id'      => $leadService->service_id,
            'lead_service_id' => $leadService->id,
            'type'            => 'income',
            'amount'          => $amount,
            'description'     => 'Alta suscripción: ' . ($service?->name ?? 'Servicio') . ' → ' . ($lead->business_name ?? 'Lead'),
            'transacted_at'   => $soldDate,
        ]);
    }
}
