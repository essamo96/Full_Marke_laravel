<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gid = DB::table('permissions_groups')->where('name','exams')->value('id'); 
$studentsGid = DB::table('permissions_groups')->where('name','students')->value('id'); 

DB::table('permissions')->insertOrIgnore([
    ['name'=>'admin.exams.results','group_id'=>$gid,'guard_name'=>'admin','created_at'=>now()],
    ['name'=>'admin.students.results','group_id'=>$studentsGid,'guard_name'=>'admin','created_at'=>now()]
]); 

$perms = DB::table('permissions')->whereIn('name', ['admin.exams.results', 'admin.students.results'])->pluck('id'); 
foreach($perms as $p) { 
    DB::table('role_has_permissions')->insertOrIgnore(['permission_id'=>$p, 'role_id'=>1]); 
}

app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

echo "Permissions added and assigned to role 1\n";
