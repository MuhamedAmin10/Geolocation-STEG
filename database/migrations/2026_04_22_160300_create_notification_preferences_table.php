<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('in_app')->default(true);
            $table->boolean('email')->default(true);
            $table->boolean('sms')->default(false);
            $table->boolean('whatsapp')->default(false);
            $table->boolean('mission_assigned')->default(true);
            $table->boolean('status_changed')->default(true);
            $table->boolean('sla_breached')->default(true);
            $table->boolean('time_reminder')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
