<?php

return [
    'types' => [
        'vacation' => 'Congés payés',
        'sick' => 'Arrêt maladie',
        'personal' => 'Congé personnel',
        'maternity' => 'Congé maternité',
        'paternity' => 'Congé paternité',
    ],
    'status' => [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
    ],
    'actions' => [
        'approve' => 'Approuver',
        'reject' => 'Rejeter',
        'view' => 'Voir',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
    ],
    'fields' => [
        'employee' => 'Employé',
        'type' => 'Type de congé',
        'start_date' => 'Date de début',
        'end_date' => 'Date de fin',
        'days_requested' => 'Jours demandés',
        'reason' => 'Motif',
        'status' => 'Statut',
        'admin_comment' => 'Commentaire administrateur',
        'created_at' => 'Créé le',
        'reject_reason' => 'Motif du refus',
    ],
];
