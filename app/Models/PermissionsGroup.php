<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission;

class PermissionsGroup extends Model
{
    use SoftDeletes;

    /**
     * Standard CRUD actions auto-generated as `admin.{module}.{action}` permissions
     * for every module-level group (same logic as yabous_org's AdminPermissionsSeeder).
     */
    public const ACTIONS = ['view', 'add', 'edit', 'delete', 'status', 'import', 'export'];

    protected $table = 'permissions_groups';

    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'color',
        'bg_color',
        'icon',
        'sort',
        'status',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(PermissionsGroup::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PermissionsGroup::class, 'parent_id');
    }

    public function mychild()
    {
        return $this->hasMany(PermissionsGroup::class, 'parent_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public static function getAllParentPermissionGroup()
    {
        return static::active()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with(['mychild' => function ($query) {
                $query->active()->orderBy('sort');
            }])
            ->get();
    }

    public function getAllPermissionGroup()
    {
        return self::active()->with('permissions')->orderBy('sort')->get();
    }

    /**
     * Ensure the view/add/edit/delete/status/import/export permissions exist for this module group,
     * named `admin.{name}.{action}`, guard `admin`.
     */
    public function generateCrudPermissions(array $actions = []): void
    {
        $actions = empty($actions) ? self::ACTIONS : $actions;
        foreach ($actions as $action) {
            Permission::firstOrCreate(
                ['name' => "admin.{$this->name}.{$action}", 'guard_name' => 'admin'],
                ['group_id' => $this->id]
            );
        }
    }
}
