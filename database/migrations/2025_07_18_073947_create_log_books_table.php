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
        Schema::create('log_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('job_description_id')->constrained('job_descriptions')->onDelete('cascade')->onUpdate('cascade');
            $table->text('description');
            $table->double('count_task')->default(1);
            $table->date('date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('client_report_id')->constrained('client_report')->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_books');
    }
};
