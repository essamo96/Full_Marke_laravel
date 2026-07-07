<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->integer('completed_courses_count')->default(0);
            $table->integer('registered_students_count')->default(0);
            $table->integer('training_hours_count')->default(0);
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['completed_courses_count', 'registered_students_count', 'training_hours_count']);
        });
    }
};
