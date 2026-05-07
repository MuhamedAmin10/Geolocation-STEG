<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mission_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->foreignId('affectation_id')->nullable()->constrained('affectations')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index(['mission_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_attachments');
    }
};
