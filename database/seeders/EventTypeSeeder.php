<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamEventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
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

        TeamEventType::updateOrCreate(
            ['team_id' => $team->id, 'name' => 'Daily'],
            [
                'icon' => 'clock',
                'default_duration_minutes' => 15,
                'default_agenda' => "Was habe ich gestern erreicht?\nWas nehme ich mir heute vor?\nGibt es Blocker?",
                'is_recurring' => true,
                'timing' => 'start',
                'track_speaking_time' => true,
                'is_retrospective' => false,
                'sort_order' => 1,
            ]
        );

        TeamEventType::updateOrCreate(
            ['team_id' => $team->id, 'name' => 'Retro'],
            [
                'icon' => 'light-bulb',
                'default_duration_minutes' => 60,
                'default_agenda' => "Was lief gut?\nWas lief nicht gut?\nWas nehmen wir uns für den nächsten Sprint vor?",
                'is_recurring' => false,
                'timing' => 'end',
                'track_speaking_time' => false,
                'is_retrospective' => true,
                'sort_order' => 2,
            ]
        );
    }
}
