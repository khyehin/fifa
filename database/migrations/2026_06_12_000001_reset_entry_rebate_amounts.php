<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('match_entries')->update(['rebate_amount' => 0]);
    }

    public function down(): void
    {
        DB::table('match_entries')->update([
            'rebate_amount' => DB::raw('ROUND(CASE WHEN black_red_amount < 0 THEN ABS(black_red_amount) * rebate_percent ELSE 0 END, 2)'),
        ]);
    }
};
