<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_event_types', function (Blueprint $table) {
            $table->boolean('is_retrospective')->default(false)->after('track_speaking_time');
        });
    }

    public function down(): void
    {
        Schema::table('team_event_types', function (Blueprint $table) {
            $table->dropColumn('is_retrospective');
        });
    }
};
