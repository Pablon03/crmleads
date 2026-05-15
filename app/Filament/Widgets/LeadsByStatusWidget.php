<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadStatus;
use Filament\Widgets\ChartWidget;

class LeadsByStatusWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Leads por estado';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $statuses = LeadStatus::orderBy('position')
            ->withCount('leads')
            ->get();

        $noStatusCount = Lead::whereNull('status_id')->count();

        $labels = $statuses->pluck('name')->toArray();
        $data   = $statuses->pluck('leads_count')->toArray();
        $colors = $statuses->pluck('color')->toArray();

        if ($noStatusCount > 0) {
            $labels[] = 'Sin estado';
            $data[]   = $noStatusCount;
            $colors[]  = '#d1d5db';
        }

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => $colors,
                    'borderWidth'     => 2,
                    'borderColor'     => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
            'cutout' => '65%',
        ];
    }
}
