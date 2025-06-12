<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LeaveStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Statistiques pour les administrateurs
            return [
                Stat::make('Total employés', User::where('role', 'employee')->count())
                    ->description('Employés actifs')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('primary'),

                Stat::make('Demandes en attente', LeaveRequest::where('status', 'pending')->count())
                    ->description('À traiter')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),

                Stat::make('Demandes ce mois', LeaveRequest::whereMonth('created_at', now()->month)->count())
                    ->description('Créées ce mois-ci')
                    ->descriptionIcon('heroicon-m-calendar')
                    ->color('success'),

                Stat::make('Demandes approuvées', LeaveRequest::where('status', 'approved')->count())
                    ->description('Total approuvées')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
            ];
        } else {
            // Statistiques pour les employés
            $userRequests = LeaveRequest::where('user_id', $user->id);

            return [
                Stat::make('Mes demandes', $userRequests->count())
                    ->description('Total de mes demandes')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('primary'),

                Stat::make('En attente', $userRequests->clone()->where('status', 'pending')->count())
                    ->description('Demandes en attente')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),

                Stat::make('Approuvées', $userRequests->clone()->where('status', 'approved')->count())
                    ->description('Demandes approuvées')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Jours pris cette année',
                    $userRequests->clone()
                        ->where('status', 'approved')
                        ->whereYear('start_date', now()->year)
                        ->sum('days_requested')
                )
                    ->description('Jours de congés utilisés')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info'),
            ];
        }
    }
}
