<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reference_points', function (Blueprint $table) {
            $table->string('meter_type', 30)->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('reference_points', function (Blueprint $table) {
            $table->dropColumn('meter_type');
        });
    }
};