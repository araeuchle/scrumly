<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::where('name', 'Demo Team')->first();

        if (! $team instanceof Team) {
            return;
        }

        $members = [
            ['first_name' => 'Anna', 'last_name' => 'Keller', 'default_capacity_percent' => 100],
            ['first_name' => 'Ben', 'last_name' => 'Schuster', 'default_capacity_percent' => 100],
            ['first_name' => 'Clara', 'last_name' => 'Vogel', 'default_capacity_percent' => 80],
        ];

        foreach ($members as $member) {
            TeamMember::updateOrCreate(
                [
                    'team_id' => $team->id,
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                ],
                ['default_capacity_percent' => $member['default_capacity_percent']]
            );
        }
    }
}
