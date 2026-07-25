<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_points', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('image')->nullable();
            $table->string('address_ar');
            $table->string('address_en');
            $table->string('working_hours_ar')->nullable();
            $table->string('working_hours_en')->nullable();
            $table->decimal('booklet_price', 8, 2)->nullable()->comment('سعر الملزمة في هذه النقطة');
            $table->string('phone')->comment('رقم جوال صاحب المحل');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_points');
    }
};
