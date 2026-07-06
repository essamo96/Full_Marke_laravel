<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$u = App\Models\Admin::where('email', 'admin@fullmarkacademy.test')->first();
if ($u) {
    echo json_encode(['id' => $u->id, 'roles' => $u->getRoleNames(), 'permissions' => $u->getAllPermissions()->pluck('name')]);
} else {
    echo "Admin not found.";
}
