<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer l'administrateur principal
        $admin = User::create([
            'name' => 'Administrateur Principal',
            'email' => 'admin@entreprise.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'department' => 'Direction',
        ]);

        // Créer des employés
        $employees = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@entreprise.com',
                'department' => 'Informatique',
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@entreprise.com',
                'department' => 'Ressources Humaines',
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@entreprise.com',
                'department' => 'Commercial',
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie.bernard@entreprise.com',
                'department' => 'Marketing',
            ],
            [
                'name' => 'Thomas Petit',
                'email' => 'thomas.petit@entreprise.com',
                'department' => 'Comptabilité',
            ],
        ];

        foreach ($employees as $employeeData) {
            $employee = User::create([
                'name' => $employeeData['name'],
                'email' => $employeeData['email'],
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'department' => $employeeData['department'],
            ]);

            // Créer quelques demandes de congés pour chaque employé
            $this->createSampleLeaveRequests($employee);
        }
    }

    private function createSampleLeaveRequests(User $employee): void
    {
        $types = ['vacation', 'sick', 'personal'];
        $statuses = ['pending', 'approved', 'rejected'];

        for ($i = 0; $i < rand(2, 5); $i++) {
            $startDate = now()->addDays(rand(-30, 60));
            $endDate = $startDate->copy()->addDays(rand(1, 10));

            LeaveRequest::create([
                'user_id' => $employee->id,
                'type' => $types[array_rand($types)],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => 'Demande de test pour ' . $employee->name,
                'status' => $statuses[array_rand($statuses)],
                'admin_comment' => rand(0, 1) ? 'Commentaire de test' : null,
            ]);
        }
    }
}
