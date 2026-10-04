<?php

namespace Database\Seeders;

use App\Enums\SprintStatus;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Database\Seeder;

class SprintSeeder extends Seeder
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

        $startsAt = now()->startOfWeek();

        Sprint::updateOrCreate(
            ['team_id' => $team->id, 'name' => 'Demo Sprint'],
            [
                'goal' => 'Den Scrum-Flow von Scrumly anhand eines Beispiel-Sprints zeigen.',
                'starts_at' => $startsAt->toDateString(),
                'ends_at' => $startsAt->copy()->addWeeks(2)->subDay()->toDateString(),
                'status' => SprintStatus::Active->value,
            ]
        );
    }
}
