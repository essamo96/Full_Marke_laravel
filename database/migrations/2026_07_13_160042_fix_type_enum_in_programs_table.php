<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. تحويل العمود إلى نص عادي لتجنب تعارض القيم القديمة والجديدة
        DB::statement("ALTER TABLE programs MODIFY COLUMN type VARCHAR(50)");
        
        // 2. تحديث أي قيم قديمة في السيرفر إلى واحدة من القيم الجديدة (primary)
        DB::statement("UPDATE programs SET type = 'primary'");

        // 3. الآن يمكننا تحويل العمود إلى نوع ENUM بالقيم الجديدة بأمان
        DB::statement("ALTER TABLE programs MODIFY COLUMN type ENUM('primary', 'middle', 'high', 'university', 'general') DEFAULT 'primary'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            //
        });
    }
};
