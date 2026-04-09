<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(
        string $action,
        string $auditableType,
        int $auditableId,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): AuditLog {
        $user = $user ?? Auth::user();

        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public function logMissionCreated(int $missionId, ?string $referenceInfo = null, ?User $user = null): void
    {
        $this->log(
            action: 'create',
            auditableType: 'Mission',
            auditableId: $missionId,
            description: "Mission created{$referenceInfo}",
            user: $user
        );
    }

    public function logMissionUpdated(int $missionId, array $oldData, array $newData, ?User $user = null): void
    {
        $changes = array_diff_assoc($newData, $oldData);

        $this->log(
            action: 'update',
            auditableType: 'Mission',
            auditableId: $missionId,
            description: 'Mission updated: ' . implode(', ', array_keys($changes)),
            oldValues: $oldData,
            newValues: $newData,
            user: $user
        );
    }

    public function logMissionAssigned(int $missionId, string $technicienName, ?User $user = null): void
    {
        $this->log(
            action: 'assign',
            auditableType: 'Mission',
            auditableId: $missionId,
            description: "Assigned to {$technicienName}",
            user: $user
        );
    }

    public function logMissionStatusChanged(int $missionId, string $oldStatus, string $newStatus, ?User $user = null): void
    {
        $this->log(
            action: 'change-status',
            auditableType: 'Mission',
            auditableId: $missionId,
            description: "Status changed from \"{$oldStatus}\" to \"{$newStatus}\"",
            oldValues: ['statut' => $oldStatus],
            newValues: ['statut' => $newStatus],
            user: $user
        );
    }

    public function logMissionDeleted(int $missionId, ?User $user = null): void
    {
        $this->log(
            action: 'delete',
            auditableType: 'Mission',
            auditableId: $missionId,
            description: 'Mission deleted',
            user: $user
        );
    }

    public function logReferencePointUpdated(int $referenceId, array $oldData, array $newData, ?User $user = null): void
    {
        $changes = array_diff_assoc($newData, $oldData);

        $this->log(
            action: 'update',
            auditableType: 'ReferencePoint',
            auditableId: $referenceId,
            description: 'Reference updated: ' . implode(', ', array_keys($changes)),
            oldValues: $oldData,
            newValues: $newData,
            user: $user
        );
    }

    public function getMissionAuditLog(int $missionId, int $limit = 20)
    {
        return AuditLog::query()
            ->where('auditable_type', 'Mission')
            ->where('auditable_id', $missionId)
            ->with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
