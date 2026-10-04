<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_speaking_turns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sprint_event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_member_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seconds')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->unique(['sprint_event_id', 'team_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_speaking_turns');
    }
};
