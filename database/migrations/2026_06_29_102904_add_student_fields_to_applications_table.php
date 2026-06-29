<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('full_name', 'full_name_en');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('full_name_ar')->after('full_name_en');
            $table->string('image')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('image');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->foreignId('branch_id')->nullable()->after('address')->constrained('branches')->nullOnDelete();
            $table->string('major_profession')->nullable()->after('branch_id');
            $table->text('health_information')->nullable()->after('major_profession');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn([
                'full_name_ar', 'image', 'date_of_birth', 'gender',
                'address', 'major_profession', 'health_information',
            ]);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('full_name_en', 'full_name');
        });
    }
};
