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
        Schema::create('program_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                ->constrained('peso_programs')
                ->cascadeOnDelete()
                ->unique();
            $table->string('focal_person_name');
            $table->string('desk_number')->nullable();
            $table->string('contact_details')->nullable();
            $table->string('department_desk')->nullable();
            $table->string('office_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_contacts');
    }
};
