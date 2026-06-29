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
        Schema::create('model_has_permission_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_group_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'mhpg_model_id_model_type_index');

            $table->foreign('permission_group_id')
                ->references('id')
                ->on('permissions_groups')
                ->onDelete('cascade');

            $table->primary(['permission_group_id', 'model_id', 'model_type'],
                'mhpg_permission_group_id_model_type_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_permission_groups');
    }
};
