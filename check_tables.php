<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tablesToCheck = [
    'users', 'students', 'guardians', 'teachers', 'programs', 'subjects', 'groups', 
    'registrations', 'payments', 'payment_registrations', 'payment_status_logs', 
    'email_verification_codes', 'model_has_permissions', 'model_has_roles', 'roles', 'permissions'
];

$existingTables = [];
foreach (Schema::getTables() as $t) {
    $existingTables[] = $t['name'];
}

$report = [];
foreach ($tablesToCheck as $table) {
    if (in_array($table, $existingTables)) {
        $columns = Schema::getColumns($table);
        $colsStr = array_map(function($c) { return $c['name'] . ' (' . $c['type_name'] . ')'; }, $columns);
        $report[$table] = $colsStr;
    } else {
        $report[$table] = 'NOT EXISTS';
    }
}

echo json_encode($report, JSON_PRETTY_PRINT);
