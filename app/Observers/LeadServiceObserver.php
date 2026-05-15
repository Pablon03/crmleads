<?php

namespace App\Observers;

use App\Models\LeadService;
use App\Models\Transaction;

class LeadServiceObserver
{
    /**
     * Al actualizar un lead_service: si el estado pasa a 'sold', crea una Transaction de income.
     * Si vuelve a otro estado, elimina la transacción asociada (si la hay).
     */
    public function updated(LeadService $leadService): void
    {
        $statusChanged = $leadService->wasChanged('status');

        if (! $statusChanged) {
            return;
        }

        $newStatus = $leadService->status;
        $oldStatus = $leadService->getOriginal('status');

        // Pasó a "vendido" → crear transacción de ingreso
        if ($newStatus === 'sold' && $oldStatus !== 'sold') {
            $this->createIncomeTransaction($leadService);
        }

        // Dejó de estar vendido → eliminar transacción automática asociada
        if ($oldStatus === 'sold' && $newStatus !== 'sold') {
            Transaction::withoutGlobalScopes()
                ->where('lead_service_id', $leadService->id)
                ->where('type', 'income')
                ->delete();
        }
    }

    /**
     * Al crear directamente con status=sold (p.ej. desde el Job de importación).
     */
    public function created(LeadService $leadService): void
    {
        if ($leadService->status === 'sold') {
            $this->createIncomeTransaction($leadService);
        }
    }

    private function createIncomeTransaction(LeadService $leadService): void
    {
        // Obtener user_id desde el lead relacionado
        $lead = $leadService->lead()->withoutGlobalScopes()->first();
        if (! $lead) {
            return;
        }

        $service  = $leadService->service()->withoutGlobalScopes()->first();
        $amount   = $leadService->sold_price ?? $service?->base_price ?? 0;
        $soldDate = $leadService->sold_at ?? now();

        Transaction::create([
            'user_id'         => $lead->user_id,
            'lead_id'         => $leadService->lead_id,
            'service_id'      => $leadService->service_id,
            'lead_service_id' => $leadService->id,
            'type'            => 'income',
            'amount'          => $amount,
            'description'     => 'Venta: ' . ($service?->name ?? 'Servicio') . ' → ' . ($lead->business_name ?? 'Lead'),
            'transacted_at'   => $soldDate,
        ]);
    }
}
