<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reference_points', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('adresse')->nullable();
            $table->string('gouvernorat')->nullable();
            $table->string('delegation')->nullable();
            $table->unsignedInteger('precision_m')->nullable();
            $table->string('statut')->default('à vérifier');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_points');
    }
};