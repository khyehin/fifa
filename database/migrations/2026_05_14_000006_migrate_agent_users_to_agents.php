<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('match_entries', 'agent_id')) {
            Schema::table('match_entries', function (Blueprint $table) {
                $table->foreignId('agent_id')->nullable()->after('football_match_id')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'agent')->orderBy('id')->get()->each(function ($user) {
                $agentId = DB::table('agents')->where('username', $user->username)->value('id');

                if (! $agentId) {
                    $agentId = DB::table('agents')->insertGetId([
                        'username' => $user->username,
                        'default_bet_amount' => $user->default_bet_amount ?? 0,
                        'my_percent' => $user->my_percent ?? 1,
                        'run_ticket' => $user->run_ticket ?? 0,
                        'remarks' => $user->remarks,
                        'is_active' => $user->is_active ?? true,
                        'bet_amount_locked' => $user->bet_amount_locked ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (Schema::hasColumn('match_entries', 'user_id')) {
                    DB::table('match_entries')
                        ->where('user_id', $user->id)
                        ->update(['agent_id' => $agentId]);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('match_entries', 'agent_id')) {
            Schema::table('match_entries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('agent_id');
            });
        }
    }
};
