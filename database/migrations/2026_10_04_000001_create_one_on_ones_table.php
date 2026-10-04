<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_on_ones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_member_id')->constrained()->cascadeOnDelete();
            $table->date('held_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('one_on_ones');
    }
};
