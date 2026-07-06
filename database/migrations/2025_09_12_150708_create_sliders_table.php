<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            
            // Texts
            $table->string('title_ar')->nullable()->comment('عنوان السلايدر بالعربية');
            $table->string('title_en')->nullable()->comment('عنوان السلايدر بالإنجليزية');
            $table->text('desc_ar')->nullable()->comment('وصف السلايدر بالعربية');
            $table->text('desc_en')->nullable()->comment('وصف السلايدر بالإنجليزية');
            
            // Buttons
            $table->string('btn1_text_ar')->nullable()->comment('نص الزر الأول بالعربية');
            $table->string('btn1_text_en')->nullable()->comment('نص الزر الأول بالإنجليزية');
            $table->string('btn1_link')->nullable()->comment('رابط الزر الأول');
            
            $table->string('btn2_text_ar')->nullable()->comment('نص الزر الثاني بالعربية');
            $table->string('btn2_text_en')->nullable()->comment('نص الزر الثاني بالإنجليزية');
            $table->string('btn2_link')->nullable()->comment('رابط الزر الثاني');
            
            // Media
            $table->string('image')->nullable()->comment('صورة الخلفية');
            $table->string('video1')->nullable()->comment('فيديو الخلفية الأول');
            $table->string('video2')->nullable()->comment('فيديو الخلفية الثاني');
            
            // Settings
            $table->integer('sort')->default(1)->comment('ترتيب العرض');
            $table->tinyInteger('status')->default(1)->comment('الحالة: فعال/غير فعال');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
