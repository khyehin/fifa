<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM match_entries'))->pluck('Key_name')->unique();

        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'match_entries'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        "))->pluck('CONSTRAINT_NAME');

        if ($foreignKeys->contains('match_entries_football_match_id_foreign')) {
            DB::statement('ALTER TABLE match_entries DROP FOREIGN KEY match_entries_football_match_id_foreign');
        }

        if ($foreignKeys->contains('match_entries_agent_id_foreign')) {
            DB::statement('ALTER TABLE match_entries DROP FOREIGN KEY match_entries_agent_id_foreign');
        }

        if ($indexes->contains('match_entries_football_match_id_user_id_unique')) {
            DB::statement('ALTER TABLE match_entries DROP INDEX match_entries_football_match_id_user_id_unique');
        }

        if ($indexes->contains('match_entries_user_id_foreign')) {
            DB::statement('ALTER TABLE match_entries DROP INDEX match_entries_user_id_foreign');
        }

        if (Schema::hasColumn('match_entries', 'user_id')) {
            DB::statement('ALTER TABLE match_entries DROP COLUMN user_id');
        }

        DB::statement('ALTER TABLE match_entries MODIFY agent_id BIGINT UNSIGNED NOT NULL');

        $indexes = collect(DB::select('SHOW INDEX FROM match_entries'))->pluck('Key_name')->unique();
        if (! $indexes->contains('match_entries_football_match_id_agent_id_unique')) {
            DB::statement('ALTER TABLE match_entries ADD UNIQUE match_entries_football_match_id_agent_id_unique (football_match_id, agent_id)');
        }

        DB::statement('ALTER TABLE match_entries ADD CONSTRAINT match_entries_football_match_id_foreign FOREIGN KEY (football_match_id) REFERENCES football_matches(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE match_entries ADD CONSTRAINT match_entries_agent_id_foreign FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        //
    }
};
