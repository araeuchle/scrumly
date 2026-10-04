<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('email', 'test@example.com')->first();

        if (! $owner instanceof User) {
            return;
        }

        Team::updateOrCreate(
            ['user_id' => $owner->id, 'name' => 'Demo Team'],
            []
        );
    }
}
