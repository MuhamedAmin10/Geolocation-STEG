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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // create, update, delete, change-status, assign
            $table->string('auditable_type'); // Mission, ReferencePoint, Affectation
            $table->unsignedBigInteger('auditable_id');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable(); // before values for updates
            $table->json('new_values')->nullable(); // after values for updates
            $table->timestamps();

            $table->index('user_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
