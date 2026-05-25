<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->date('settlement_date')->index();
            $table->decimal('total_bet_amount', 12, 2)->default(0);
            $table->decimal('total_black_red', 12, 2)->default(0);
            $table->decimal('total_my_winlose', 12, 2)->default(0);
            $table->decimal('total_run_ticket', 12, 2)->default(0);
            $table->decimal('net_total', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
