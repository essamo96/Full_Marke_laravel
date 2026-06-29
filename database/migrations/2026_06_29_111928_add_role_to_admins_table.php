<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Coarse-grained role kept alongside the existing Spatie permission
            // groups — used for the quick "accountant only sees financials" gate.
            $table->enum('role', ['super_admin', 'admin', 'accountant'])->default('admin')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
