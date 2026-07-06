<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$admins = DB::table('admins')->get();
foreach ($admins as $admin) {
    $json = json_encode($admin);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error in Admin ID: {$admin->id} - " . json_last_error_msg() . "\n";
        // Fix by converting all strings to UTF-8
        $update = [];
        foreach (get_object_vars($admin) as $key => $val) {
            if (is_string($val)) {
                $update[$key] = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
            }
        }
        DB::table('admins')->where('id', $admin->id)->update($update);
        echo "Fixed JSON error for Admin ID: {$admin->id}\n";
    }
}
echo "Done JSON check.\n";
