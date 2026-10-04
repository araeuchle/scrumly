<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sprint_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sprint_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_event_type_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->unsignedSmallInteger('duration_minutes');
            $table->text('agenda')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sprint_events');
    }
};
