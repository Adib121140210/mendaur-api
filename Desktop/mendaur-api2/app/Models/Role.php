<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_role',
        'level_akses',
        'deskripsi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'id');
    }

    /**
     * Check if this role has a specific permission
     */
    public function hasPermission(string $permissionCode): bool
    {
        return $this->permissions()
            ->where('permission_code', $permissionCode)
            ->exists();
    }

    /**
     * Get all permission codes for this role
     */
    public function getPermissionCodes(): array
    {
        return $this->permissions()
            ->pluck('permission_code')
            ->toArray();
    }

    /**
     * Scope: Get all permissions including inherited from lower roles
     * e.g., admin gets all nasabah permissions + admin permissions
     */
    public function getInheritedPermissions()
    {
        $currentLevel = $this->level_akses;

        // Get all roles at current level and below
        $inheritedRoles = Role::where('level_akses', '<=', $currentLevel)->get();

        // Collect all permissions
        $permissions = collect();
        foreach ($inheritedRoles as $role) {
            $permissions = $permissions->concat($role->permissions);
        }

        return $permissions->unique('permission_code');
    }

    /**
     * Static method: Get role by name
     */
    public static function getByName(string $name): ?self
    {
        return self::where('nama_role', $name)->first();
    }
}
