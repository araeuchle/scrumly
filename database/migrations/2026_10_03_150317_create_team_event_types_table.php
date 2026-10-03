<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_event_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->default('calendar-days');
            $table->unsignedSmallInteger('default_duration_minutes')->default(30);
            $table->text('default_agenda')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('timing')->default('start');
            $table->boolean('track_speaking_time')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_event_types');
    }
};
