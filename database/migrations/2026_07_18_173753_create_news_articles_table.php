<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->longText('content_ar')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('admin_id');
            $table->boolean('status')->default(1);
            $table->integer('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('news_categories')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('tag_id');
            $table->foreign('article_id')->references('id')->on('news_articles')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('news_tags')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('news_articles');
    }
};
