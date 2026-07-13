<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Programs
        Schema::table('programs', function (Blueprint $table) {
            $table->renameColumn('title_ar', 'name_ar');
            $table->renameColumn('title_en', 'name_en');
            $table->renameColumn('order', 'sort_order');
            $table->renameColumn('description_en', 'short_description');
            $table->renameColumn('description_ar', 'long_description');
        });

        // 2. Subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->renameColumn('order', 'sort_order');
        });

        // 3. Guardians
        Schema::table('guardians', function (Blueprint $table) {
            $table->renameColumn('full_name', 'name');
        });

        // 4. Registrations
        Schema::table('registrations', function (Blueprint $table) {
            $table->renameColumn('total_fee', 'fee_snapshot');
        });
        
        // We have an enum in registrations for status, we need to alter it directly via DB statement or add a new column and drop old
        DB::statement("ALTER TABLE registrations CHANGE COLUMN registration_status status ENUM('pending', 'partially_paid', 'fully_paid') DEFAULT 'pending'");
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });

        // 5. Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('total_amount', 'amount');
            $table->renameColumn('payment_method_id', 'method');
            $table->string('invoice_number')->nullable()->after('status')->unique();
            $table->renameColumn('confirmed_by', 'reviewed_by');
            $table->renameColumn('confirmed_at', 'reviewed_at');
        });

        // 6. Payment Registrations
        Schema::create('payment_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->decimal('allocated_amount', 10, 2);
            $table->timestamps();
        });

        // 7. Payment Status Logs
        Schema::create('payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('action'); // approved, rejected, pending
            $table->foreignId('by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('at');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_status_logs');
        Schema::dropIfExists('payment_registrations');

        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('amount', 'total_amount');
            $table->renameColumn('method', 'payment_method_id');
            $table->dropColumn('invoice_number');
            $table->renameColumn('reviewed_by', 'confirmed_by');
            $table->renameColumn('reviewed_at', 'confirmed_at');
        });

        DB::statement("ALTER TABLE registrations CHANGE COLUMN status registration_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");

        Schema::table('registrations', function (Blueprint $table) {
            $table->renameColumn('fee_snapshot', 'total_fee');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'fully_paid'])->default('unpaid');
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->renameColumn('name', 'full_name');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->renameColumn('sort_order', 'order');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->renameColumn('name_ar', 'title_ar');
            $table->renameColumn('name_en', 'title_en');
            $table->renameColumn('sort_order', 'order');
            $table->renameColumn('short_description', 'description_en');
            $table->renameColumn('long_description', 'description_ar');
        });
    }
};
