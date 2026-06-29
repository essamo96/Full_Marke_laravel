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
    public const ACTIONS = ['view', 'add', 'edit', 'delete', 'status'];

    protected $table = 'permissions_groups';

    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'color',
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
            ->with(['children' => function ($query) {
                $query->active()->orderBy('sort');
            }])
            ->get();
    }

    /**
     * Ensure the view/add/edit/delete/status permissions exist for this module group,
     * named `admin.{name}.{action}`, guard `admin`.
     */
    public function generateCrudPermissions(): void
    {
        foreach (self::ACTIONS as $action) {
            Permission::firstOrCreate(
                ['name' => "admin.{$this->name}.{$action}", 'guard_name' => 'admin'],
                ['group_id' => $this->id]
            );
        }
    }
}
