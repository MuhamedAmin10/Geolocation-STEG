<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            if (!Schema::hasColumn('missions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('due_at');
            }

            if (!Schema::hasColumn('missions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }

            if (!Schema::hasColumn('missions', 'total_working_time')) {
                $table->integer('total_working_time')->nullable()->after('completed_at');
            }

            if (!Schema::hasColumn('missions', 'estimated_duration')) {
                $table->integer('estimated_duration')->nullable()->after('total_working_time');
            }

            if (!Schema::hasColumn('missions', 'travel_time_minutes')) {
                $table->integer('travel_time_minutes')->nullable()->after('estimated_duration');
            }

            if (!Schema::hasColumn('missions', 'on_site_time_minutes')) {
                $table->integer('on_site_time_minutes')->nullable()->after('travel_time_minutes');
            }

            if (!Schema::hasColumn('missions', 'efficiency_score')) {
                $table->decimal('efficiency_score', 5, 2)->nullable()->after('on_site_time_minutes');
            }

            if (!Schema::hasColumn('missions', 'sla_level')) {
                $table->string('sla_level', 20)->default('Bronze')->after('efficiency_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $columns = [
                'total_working_time',
                'estimated_duration',
                'travel_time_minutes',
                'on_site_time_minutes',
                'efficiency_score',
                'sla_level',
            ];

            $toDrop = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('missions', $column)));

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
