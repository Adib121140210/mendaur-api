<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model AuditLog - Tabel audit_logs
 *
 * Mencatat semua aksi admin untuk audit trail
 */
class AuditLog extends Model
{
    protected $primaryKey = 'audit_log_id';
    public $incrementing = true;
    protected $keyType = 'int';
    use HasFactory;

    protected $fillable = [
        'admin_id',       // ID admin yang melakukan aksi
        'action_type',    // Tipe aksi: create, update, delete, approve, reject
        'resource_type',  // Tipe resource: TabungSampah, PenarikanTunai, dll
        'resource_id',    // ID resource yang diubah
        'old_values',     // Nilai sebelum perubahan (JSON)
        'new_values',     // Nilai setelah perubahan (JSON)
        'reason',         // Alasan melakukan aksi
        'ip_address',     // IP address admin
        'user_agent',     // Browser/device info
        'status',         // Status: success, failed
        'error_message',  // Pesan error jika gagal
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relationships
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    /**
     * Static method: Log an admin action
     *
     * @param User $admin
     * @param string $actionType (create, update, delete, approve, reject, etc)
     * @param string $resourceType (TabungSampah, PenarikanTunai, etc)
     * @param int $resourceId
     * @param array $oldValues
     * @param array $newValues
     * @param string $reason
     * @param bool $success
     * @param string|null $errorMessage
     * @return AuditLog
     */
    public static function logAction(
        User $admin,
        string $actionType,
        string $resourceType,
        int $resourceId,
        array $oldValues = [],
        array $newValues = [],
        string $reason = '',
        bool $success = true,
        ?string $errorMessage = null
    ): self {
        // Get request IP
        $ipAddress = request()->ip();

        // Get user agent
        $userAgent = request()->userAgent();

        return self::create([
            'admin_id' => $admin->id,
            'action_type' => $actionType,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'reason' => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $success ? 'success' : 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Scope: Get logs by resource type
     */
    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope: Get logs by admin
     */
    public function scopeByAdmin($query, User $admin)
    {
        return $query->where('admin_id', $admin->id);
    }

    /**
     * Scope: Get logs by action type
     */
    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope: Get successful logs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Get failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
