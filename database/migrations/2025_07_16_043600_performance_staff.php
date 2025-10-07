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
        Schema::create('performance_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_reference_id')->constrained('performance_review_references')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade')->onUpdate('cascade');
            $table->double('supervisor_score');
            $table->double('self_score');
            $table->timestamps();   
            $table->softDeletes('deleted_at');
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_staff');
    }
};
