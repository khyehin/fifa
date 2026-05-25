<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agents')) {
            return;
        }

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->decimal('default_bet_amount', 12, 2)->default(0);
            $table->decimal('my_percent', 8, 4)->default(1);
            $table->decimal('run_ticket', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('bet_amount_locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
