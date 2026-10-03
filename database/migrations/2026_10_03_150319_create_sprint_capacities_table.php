<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sprint_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('capacity_percent')->default(100);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['sprint_id', 'team_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sprint_capacities');
    }
};
