<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$actions = ['view', 'add', 'edit', 'delete', 'status', 'import', 'export'];
$groups = App\Models\PermissionsGroup::where('parent_id', '!=', 0)->get();

$missing = [];
foreach($groups as $g) {
    $perms = $g->permissions->pluck('name')->toArray();
    $m = [];
    foreach($actions as $a) {
        $n = 'admin.'.$g->name.'.'.$a;
        if(!in_array($n, $perms)) {
            $m[] = $a;
            
            // create missing permissions right now as well
            \Spatie\Permission\Models\Permission::create([
                'name' => $n,
                'guard_name' => 'admin',
                'group_id' => $g->id
            ]);
        }
    }
    if(!empty($m)) {
        $missing[$g->name] = $m;
    }
}
echo json_encode($missing);
