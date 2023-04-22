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
        Schema::create('course_subjects', function (Blueprint $table) {
            $table->id();
            //$table->timestamps();
            $table->foreignId('university_course')->constrained('university_courses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('subject')->constrained('subjects')->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('advanced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_subjects');
    }
};
