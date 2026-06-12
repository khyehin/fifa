<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_weekly_rebates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->decimal('rebate_percent', 8, 4)->default(0);
            $table->timestamps();
            $table->unique(['agent_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_weekly_rebates');
    }
};
