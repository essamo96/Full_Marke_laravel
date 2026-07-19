<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE subject_resources MODIFY type ENUM('video', 'document', 'image', 'link', 'zoom')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subject_resources MODIFY type ENUM('video', 'document', 'link', 'zoom')");
    }
};
