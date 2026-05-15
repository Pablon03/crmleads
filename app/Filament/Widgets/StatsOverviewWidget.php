<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        $userId = auth()->id();

        // Leads totales del usuario
        $totalLeads = Lead::count();

        // Estado "Contactado" (position 2 por defecto)
        $contactedStatus = LeadStatus::where('name', 'Contactado')->first();
        $contactedThisMonth = $contactedStatus
            ? Lead::where('status_id', $contactedStatus->id)
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count()
            : 0;

        // Estado "Cerrado ganado" (position 5 por defecto)
        $wonStatus = LeadStatus::where('name', 'Cerrado ganado')->first();
        $closedThisMonth = $wonStatus
            ? Lead::where('status_id', $wonStatus->id)
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count()
            : 0;

        // Ingresos del mes en curso
        $incomeThisMonth = Transaction::where('type', 'income')
            ->whereMonth('transacted_at', now()->month)
            ->whereYear('transacted_at', now()->year)
            ->sum('amount');

        // Ingresos del mes anterior para calcular tendencia
        $incomePrevMonth = Transaction::where('type', 'income')
            ->whereMonth('transacted_at', now()->subMonth()->month)
            ->whereYear('transacted_at', now()->subMonth()->year)
            ->sum('amount');

        $incomeChange = $incomePrevMonth > 0
            ? round((($incomeThisMonth - $incomePrevMonth) / $incomePrevMonth) * 100, 1)
            : 0;

        return [
            Stat::make('Leads totales', $totalLeads)
                ->description('En todas las carpetas')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Contactados este mes', $contactedThisMonth)
                ->description('Leads contactados en ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-phone')
                ->color('info'),

            Stat::make('Cerrados este mes', $closedThisMonth)
                ->description('Ventas ganadas en ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Ingresos del mes', '€' . number_format((float) $incomeThisMonth, 2, ',', '.'))
                ->description(
                    $incomeChange >= 0
                        ? "+{$incomeChange}% respecto al mes anterior"
                        : "{$incomeChange}% respecto al mes anterior"
                )
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'danger'),
        ];
    }
}
