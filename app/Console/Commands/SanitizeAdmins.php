<?php

namespace App\Console\Commands; use Illuminate\Console\Command; use Illuminate\Support\Facades\DB; class SanitizeAdmins extends Command { protected $signature = 'sanitize:admins'; public function handle() { $admins = DB::table('admins')->get(); foreach ($admins as $admin) { $name = $admin->name; if (!mb_check_encoding($name, 'UTF-8')) { $safeName = mb_convert_encoding($name, 'UTF-8', 'auto'); DB::table('admins')->where('id', $admin->id)->update(['name' => $safeName]); $this->info('Fixed ' . $admin->id); } } $this->info('Done'); } }
