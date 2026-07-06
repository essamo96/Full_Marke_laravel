<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name', 'group_id', 'guard_name',
    ];

    // Relationship
    public function permission_group()
    {
        return $this->hasOne(PermissionsGroup::class, 'id', 'group_id');
    }

    // Add new permission
    public function addPermission($name, $group_id, $guard_name)
    {
        $this->name = $name;
        $this->group_id = $group_id;
        $this->guard_name = $guard_name;

        $this->save();
        return $this;
    }

    // Update existing permission
    public function updatePermission($obj, $name, $group_id, $guard_name)
    {
        $obj->name = $name;
        $obj->group_id = $group_id;
        $obj->guard_name = $guard_name;

        $obj->save();
        return $obj;
    }

    // Delete permission
    public function deletePermission($obj)
    {
        return $obj->delete();
    }

    // Get permission by ID
    public function getPermissions($id)
    {
        return $this->find($id);
    }

    // Get all permissions with optional name filter
    public function getAllPermissions($name = null)
    {
        return $this->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'LIKE', '%' . $name . '%');
            }
        })->get();
    }
}
