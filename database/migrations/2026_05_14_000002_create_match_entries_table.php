<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('football_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->decimal('bet_amount', 12, 2)->default(0);
            $table->string('ha', 10)->nullable();
            $table->string('ou', 10)->nullable();
            $table->decimal('black_red_amount', 12, 2)->default(0);
            $table->decimal('my_percent', 8, 4)->default(1);
            $table->decimal('my_winlose', 12, 2)->default(0);
            $table->decimal('run_ticket', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['football_match_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_entries');
    }
};
