<?php

namespace Database\Seeders;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()->count(10)->create([
            'password' => Hash::make('password'),
        ]);
        $preferenceIds = Preference::pluck('id');
        $users->each(function ($user) use ($preferenceIds) {
            $preferenceIds->each(function ($preferenceId) use ($user) {
                $option = rand(1, 3);
                switch ($option) {
                    case 1:
                        $user->preferences()->attach($preferenceId, [
                            'status' => Preference::STATUS_PREFERRED,
                        ]);
                        break;
                    case 2:
                        $user->preferences()->attach($preferenceId, [
                            'status' => Preference::STATUS_DISLIKED,
                        ]);
                        break;
                    case 3:
                        $user->preferences()->attach($preferenceId, [
                            'status' => Preference::STATUS_INDIFFERENT,
                        ]);
                }
            });

        });

    }
}
