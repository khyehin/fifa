<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\FootballMatch;
use App\Models\MatchEntry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@fifa.local',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        $agents = collect([
            ['username' => 'agent01', 'default_bet_amount' => 500, 'my_percent' => 0.50, 'run_ticket' => 10],
            ['username' => 'agent02', 'default_bet_amount' => 800, 'my_percent' => 0.40, 'run_ticket' => 20],
            ['username' => 'agent03', 'default_bet_amount' => 300, 'my_percent' => 0.60, 'run_ticket' => 0],
        ])->map(fn ($agent) => Agent::updateOrCreate(
            ['username' => $agent['username']],
            $agent + [
                'is_active' => true,
            ]
        ));

        $match = FootballMatch::updateOrCreate(
            ['match_date' => now()->toDateString(), 'title' => 'Slovakia vs Romania'],
            ['home_team' => 'Slovakia', 'away_team' => 'Romania', 'remarks' => 'Sample match']
        );

        foreach ($agents as $index => $agent) {
            MatchEntry::updateOrCreate(
                ['football_match_id' => $match->id, 'agent_id' => $agent->id],
                [
                    'bet_amount' => $agent->default_bet_amount,
                    'ha' => $index % 2 === 0 ? 'H' : 'A',
                    'ou' => $index % 2 === 0 ? 'O' : 'U',
                    'black_red_amount' => [700, -350, 120][$index],
                    'my_percent' => $agent->my_percent,
                    'run_ticket' => $agent->run_ticket,
                ]
            );
        }
    }
}
