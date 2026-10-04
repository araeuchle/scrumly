<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sprint_events', function (Blueprint $table) {
            $table->string('board_url')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('sprint_events', function (Blueprint $table) {
            $table->dropColumn('board_url');
        });
    }
};
