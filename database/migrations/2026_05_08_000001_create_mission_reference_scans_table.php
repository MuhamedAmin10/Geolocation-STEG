<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mission_reference_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reference_point_id')->nullable()->constrained('reference_points')->nullOnDelete();
            $table->foreignId('technicien_id')->constrained()->cascadeOnDelete();
            $table->string('reference_code');
            $table->string('compteur_type', 30);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy_m', 8, 2)->nullable();
            $table->decimal('distance_m', 8, 2)->nullable();
            $table->boolean('is_match')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();

            $table->index(['mission_id', 'scanned_at']);
            $table->index(['technicien_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_reference_scans');
    }
};