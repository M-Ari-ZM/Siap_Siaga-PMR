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
        Schema::create('handling_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_id')->unique()->constrained('emergencies')->cascadeOnDelete();
            $table->foreignId('pmr_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('action_taken');
            $table->text('notes')->nullable();
            $table->string('documentation_photo')->nullable();
            $table->string('final_condition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handling_reports');
    }
};
