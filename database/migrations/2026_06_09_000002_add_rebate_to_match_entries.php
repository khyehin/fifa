<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('match_entries', 'rebate_percent')) {
                $table->decimal('rebate_percent', 8, 4)->default(0)->after('my_winlose');
            }

            if (! Schema::hasColumn('match_entries', 'rebate_amount')) {
                $table->decimal('rebate_amount', 12, 2)->default(0)->after('rebate_percent');
            }
        });

        DB::table('match_entries')->update([
            'rebate_amount' => DB::raw('ROUND(CASE WHEN black_red_amount < 0 THEN ABS(black_red_amount) * rebate_percent ELSE 0 END, 2)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('match_entries', function (Blueprint $table) {
            if (Schema::hasColumn('match_entries', 'rebate_amount')) {
                $table->dropColumn('rebate_amount');
            }

            if (Schema::hasColumn('match_entries', 'rebate_percent')) {
                $table->dropColumn('rebate_percent');
            }
        });
    }
};
