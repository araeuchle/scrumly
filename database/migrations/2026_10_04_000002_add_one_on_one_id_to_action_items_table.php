<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('action_items', function (Blueprint $table) {
            $table->foreignUuid('one_on_one_id')->nullable()->after('sprint_event_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('action_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('one_on_one_id');
        });
    }
};
