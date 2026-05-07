<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mission_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('techniciens')->cascadeOnDelete();
            $table->enum('action', ['start_work', 'pause', 'resume', 'complete', 'break_start', 'break_end']);
            $table->timestamp('logged_at')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['mission_id', 'technician_id', 'logged_at'], 'mission_tech_logged_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_time_logs');
    }
};
