<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reference_collection_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reference_point_id')->nullable()->constrained('reference_points')->nullOnDelete();
            $table->foreignId('technicien_id')->constrained()->cascadeOnDelete();
            $table->string('reference_code');
            $table->string('meter_type', 30);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy_m', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('was_created')->default(false);
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();

            $table->index(['reference_code', 'scanned_at']);
            $table->index(['technicien_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_collection_scans');
    }
};