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
        Schema::create('emergency_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_id')->constrained('emergencies')->cascadeOnDelete();
            $table->foreignId('pmr_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['offered', 'accepted', 'declined', 'completed'])->default('offered');
            $table->integer('distance_meters')->nullable();
            $table->float('dispatch_score')->nullable();
            $table->timestamp('offered_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_assignments');
    }
};
