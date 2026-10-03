<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\PermissionsGroup;

return new class extends Migration
{
    public function up(): void
    {
        $examsGroup = PermissionsGroup::where('name', 'exams')->where('parent_id', '!=', 0)->first();
        $parentGroup = PermissionsGroup::where('name', 'exams_parent')->where('parent_id', 0)->first();

        if (!$examsGroup) {
            return;
        }

        // Relink orphaned admin.exams.view (left on soft-deleted root group) to current child group.
        $viewPermission = Permission::firstOrCreate(
            ['name' => 'admin.exams.view', 'guard_name' => 'admin'],
            ['group_id' => $examsGroup->id]
        );

        if ((int) $viewPermission->group_id !== (int) $examsGroup->id) {
            $viewPermission->group_id = $examsGroup->id;
            $viewPermission->save();
        }

        if ($parentGroup) {
            $parentView = Permission::firstOrCreate(
                ['name' => 'admin.exams_parent.view', 'guard_name' => 'admin'],
                ['group_id' => $parentGroup->id]
            );

            if ((int) $parentView->group_id !== (int) $parentGroup->id) {
                $parentView->group_id = $parentGroup->id;
                $parentView->save();
            }
        }

        // Any role that already has exam management actions should also get view
        // (and parent view) so the sidebar item becomes visible without re-saving the form.
        $actionPermissionIds = Permission::where('guard_name', 'admin')
            ->where('name', 'like', 'admin.exams.%')
            ->where('name', '!=', 'admin.exams.view')
            ->pluck('id');

        if ($actionPermissionIds->isEmpty()) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            return;
        }

        $roleIds = DB::table('role_has_permissions')
            ->whereIn('permission_id', $actionPermissionIds)
            ->pluck('role_id')
            ->unique();

        $grantNames = ['admin.exams.view'];
        if ($parentGroup) {
            $grantNames[] = 'admin.exams_parent.view';
        }

        foreach ($roleIds as $roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $role->givePermissionTo($grantNames);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally left empty: re-orphaning the view permission would break the sidebar again.
    }
};
