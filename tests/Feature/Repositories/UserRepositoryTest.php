<?php

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
    }

    public function test_it_updates_profile_handicap()
    {
        $user = User::factory()->create();

        $attributes = [
            'handicap' => 8.2,
        ];

        $this->userRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('user_profiles', $attributes);
    }

    public function test_it_updates_dob()
    {
        $user = User::factory()->create();

        $attributes = [
            'dob' => "1972-12-31",
        ];

        $this->userRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('user_profiles', $attributes);
    }

    public function test_it_updates_profile_date_of_birth()
    {
        $user = User::factory()->create();

        $attributes = [
            'handicap' => 8.2,
            'dob' => "1970-12-31"
        ];

        $this->userRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('user_profiles', $attributes);
    }

    public function test_it_updates_account_name()
    {
        $user = User::factory()->create();

        $attributes = [
            'name' => "new name",
        ];

        $this->userRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('users', $attributes);
    }

    public function test_it_returns_user_with_profile()
    {
        $user = User::factory()->create([
            'name' => 'new name',
            'email' => 'new@email.com',
            'password' => Hash::make('password'),
        ]);

        $userProfile = UserProfile::firstWhere('user_id', $user->id);
        $userProfile->update(['handicap'=>8.2]);
        $this->assertDatabaseHas('user_profiles', $userProfile->toArray());

        $result = $this->userRepository->show($user->id);
        $this->assertEquals($user->name, $result->toArray(request())['name']);
        $this->assertEquals($user->email, $result->toArray(request())['email']);
        $this->assertEquals($userProfile->handicap, $result->toArray(request())['handicap']);
    }
}
