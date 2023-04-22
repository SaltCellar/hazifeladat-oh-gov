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
        Schema::create('university_courses', function (Blueprint $table) {
            $table->id();
            //$table->timestamps();
            $table->foreignId('university')->constrained('universities')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('faculty')->constrained('faculties')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('course')->constrained('courses')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreignId('subject')->constrained('subjects')->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('subject_advanced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_courses');
    }
};
