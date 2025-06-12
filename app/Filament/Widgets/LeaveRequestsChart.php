<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class LeaveRequestsChart extends ChartWidget
{
    protected static ?string $heading = 'Demandes de congés par mois';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 2;


    public static function canView(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = LeaveRequest::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Demandes créées',
                    'data' => $data,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
