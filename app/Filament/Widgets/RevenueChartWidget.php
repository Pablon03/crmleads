<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Ingresos por mes';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Últimos 12 meses
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i));

        $labels = $months->map(fn (Carbon $date) => ucfirst($date->translatedFormat('M y')))->toArray();

        $income = $months->map(function (Carbon $date) {
            return (float) Transaction::where('type', 'income')
                ->whereYear('transacted_at', $date->year)
                ->whereMonth('transacted_at', $date->month)
                ->sum('amount');
        })->toArray();

        $expenses = $months->map(function (Carbon $date) {
            return (float) Transaction::where('type', 'expense')
                ->whereYear('transacted_at', $date->year)
                ->whereMonth('transacted_at', $date->month)
                ->sum('amount');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Ingresos (€)',
                    'data'            => $income,
                    'backgroundColor' => 'rgba(34,197,94,0.6)',
                    'borderColor'     => 'rgb(34,197,94)',
                    'borderWidth'     => 2,
                ],
                [
                    'label'           => 'Gastos (€)',
                    'data'            => $expenses,
                    'backgroundColor' => 'rgba(239,68,68,0.4)',
                    'borderColor'     => 'rgb(239,68,68)',
                    'borderWidth'     => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => '(value) => "€" + value.toLocaleString("es-ES")',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
        ];
    }
}
