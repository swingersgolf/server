<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoControllerTest extends TestCase
{
    public function test_user_can_upload_profile_photo()
    {
        Storage::fake('s3');
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('api.v1.profile-photo.store'), [
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertOk();

        Storage::disk('s3')->assertExists('profile-photos/' . basename($user->fresh()->profile_photo_path));
    }

    public function test_user_can_delete_profile_photo()
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        $user->userProfile->profile_photo_path = 'profile-photos/photo.jpg';

        Storage::disk('s3')->put('profile-photos/photo.jpg', 'dummy-content');

        $this->actingAs($user)->deleteJson(route('api.v1.profile-photo.destroy'))->assertOk();

        Storage::disk('s3')->assertMissing('profile-photos/photo.jpg');
        $this->assertNull($user->fresh()->profile_photo_path);
    }
}
