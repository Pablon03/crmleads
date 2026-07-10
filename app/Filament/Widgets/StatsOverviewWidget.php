<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadService;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // ── Núcleo de suscripción ────────────────────────────────────────────
        // Suscripciones activas (status = sold) con su plan cargado.
        $activeSubs = LeadService::query()
            ->where('status', 'sold')
            ->with('service')
            ->get();

        $mrr = $activeSubs->sum(fn (LeadService $ls) => $ls->monthlyValue());

        $activeClients = Lead::whereHas('leadServices', fn ($q) => $q->where('status', 'sold'))->count();

        $arpu = $activeClients > 0 ? $mrr / $activeClients : 0;

        // Bajas (churn) de este mes.
        $churnThisMonth = LeadService::query()
            ->where('status', 'churned')
            ->whereMonth('canceled_at', now()->month)
            ->whereYear('canceled_at', now()->year)
            ->count();

        // Altas (nuevos clientes activos) de este mes.
        $newClientsThisMonth = LeadService::query()
            ->where('status', 'sold')
            ->whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->count();

        // ── Caja del mes (transacciones) ─────────────────────────────────────
        $incomeThisMonth = Transaction::where('type', 'income')
            ->whereMonth('transacted_at', now()->month)
            ->whereYear('transacted_at', now()->year)
            ->sum('amount');

        $incomePrevMonth = Transaction::where('type', 'income')
            ->whereMonth('transacted_at', now()->subMonth()->month)
            ->whereYear('transacted_at', now()->subMonth()->year)
            ->sum('amount');

        $incomeChange = $incomePrevMonth > 0
            ? round((($incomeThisMonth - $incomePrevMonth) / $incomePrevMonth) * 100, 1)
            : 0;

        $totalLeads = Lead::count();

        return [
            Stat::make('MRR', '€' . number_format($mrr, 0, ',', '.'))
                ->description('Ingresos recurrentes mensuales')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),

            Stat::make('Clientes activos', $activeClients)
                ->description($newClientsThisMonth . ' alta/s en ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Ticket medio (ARPU)', '€' . number_format($arpu, 0, ',', '.'))
                ->description('MRR por cliente activo')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Bajas este mes', $churnThisMonth)
                ->description('Clientes dados de baja en ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($churnThisMonth > 0 ? 'danger' : 'gray'),

            Stat::make('Leads totales', $totalLeads)
                ->description('En el pipeline del equipo')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('gray'),

            Stat::make('Caja del mes', '€' . number_format((float) $incomeThisMonth, 0, ',', '.'))
                ->description(
                    ($incomeChange >= 0 ? "+{$incomeChange}%" : "{$incomeChange}%") . ' vs mes anterior'
                )
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'danger'),
        ];
    }
}
