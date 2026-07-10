<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadService;
use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3;

    protected function getViewData(): array
    {
        $activeSubs = LeadService::query()
            ->where('status', 'sold')
            ->with('service')
            ->get();

        $mrr = $activeSubs->sum(fn (LeadService $ls) => $ls->monthlyValue());

        $activeClients = Lead::whereHas('leadServices', fn ($q) => $q->where('status', 'sold'))->count();

        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 6  => 'Buenas noches',
            $hour < 14 => 'Buenos días',
            $hour < 21 => 'Buenas tardes',
            default    => 'Buenas noches',
        };

        return [
            'greeting'      => $greeting,
            'name'          => auth()->user()?->name,
            'mrr'           => $mrr,
            'activeClients' => $activeClients,
            'today'         => now()->translatedFormat('l, d \d\e F'),
        ];
    }
}
