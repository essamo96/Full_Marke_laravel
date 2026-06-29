<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // `fee` already exists. `min_payment` is the minimum amount accepted
            // to activate a registration (min_payment <= fee, enforced in FormRequest).
            $table->decimal('min_payment', 10, 2)->nullable()->after('fee');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('min_payment');
        });
    }
};
