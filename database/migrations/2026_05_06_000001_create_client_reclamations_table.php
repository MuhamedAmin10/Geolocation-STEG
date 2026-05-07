<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_reclamations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('reference_id')->nullable()->constrained('reference_points')->nullOnDelete();
            $table->string('compteur_reference');
            $table->string('subject');
            $table->text('description');
            $table->string('status')->default('Nouveau');
            $table->foreignId('mission_id')->nullable()->constrained('missions')->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['reference_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_reclamations');
    }
};