<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $connection = 'fleet_db';

    protected $fillable = ['name', 'guard_name'];

    protected $attributes = [
        'guard_name' => 'web',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function givePermissionTo($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : func_get_args();
        $permissionModels = Permission::whereIn('name', $permissions)->get();
        $this->permissions()->syncWithoutDetaching($permissionModels->pluck('id'));
    }

    public static function findByName(string $name, string $guardName = null): ?self
    {
        $query = self::where('name', $name);

        if ($guardName) {
            $query->where('guard_name', $guardName);
        }

        return $query->first();
    }
}
