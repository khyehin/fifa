<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('match_entries')->update([
            'my_winlose' => DB::raw('ROUND(black_red_amount * my_percent * -1, 2)'),
        ]);
    }

    public function down(): void
    {
        DB::table('match_entries')->update([
            'my_winlose' => DB::raw('ROUND(black_red_amount * my_percent, 2)'),
        ]);
    }
};
