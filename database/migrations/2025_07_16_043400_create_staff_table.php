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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('birth_place');
            $table->date('birth_date');
            $table->text('address');
            $table->string('no_ktp', 20);
            $table->string('no_spk');
            $table->string('phone', 20)->nullable();
            $table->enum('jenjang', ['SMA', 'D-3', 'D-4', 'S-1', 'S-2', 'S-3']);
            $table->string('jurusan');
            $table->string('university');
            $table->string('no_ijazah');
            $table->date('tmt_training');
            $table->string('periode', 20);
            $table->date('selesai_training');
            $table->foreignId('department_reference_id')->nullable()->constrained('department_references')->onDelete('set null');
            $table->foreignId('position_reference_id')->nullable()->constrained('position_references')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
}; 