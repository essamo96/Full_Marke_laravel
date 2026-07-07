<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['pages', 'faqs', 'teams'];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign($tableName . '_company_id_foreign');
                    $table->dropColumn('company_id');
                });
            }
        }
        
        Schema::dropIfExists('companies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
