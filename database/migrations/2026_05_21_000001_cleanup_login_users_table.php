<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array('user_id', Schema::getColumnListing('match_entries'), true)) {
            try {
                Schema::table('match_entries', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('user_id');
                });
            } catch (Throwable) {
                try {
                    Schema::table('match_entries', function (Blueprint $table) {
                        $table->dropColumn('user_id');
                    });
                } catch (Throwable) {
                    //
                }
            }
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['default_bet_amount', 'my_percent', 'run_ticket', 'remarks', 'bet_amount_locked'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        DB::table('sessions')->delete();
        DB::table('password_reset_tokens')->delete();
        DB::table('users')->delete();

        DB::table('users')->insert([
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@login.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => true,
            'password_changed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'default_bet_amount')) {
                $table->decimal('default_bet_amount', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('users', 'my_percent')) {
                $table->decimal('my_percent', 8, 4)->default(1);
            }
            if (! Schema::hasColumn('users', 'run_ticket')) {
                $table->decimal('run_ticket', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('users', 'remarks')) {
                $table->text('remarks')->nullable();
            }
            if (! Schema::hasColumn('users', 'bet_amount_locked')) {
                $table->boolean('bet_amount_locked')->default(false);
            }
        });
    }
};
