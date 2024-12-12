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
    public function test_generate_upload_url()
    {
        Storage::fake('s3');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.v1.profile-photo.upload-url'))
            ->assertOk()
            ->assertJsonStructure(['upload_url', 'file_path']);
    }
    public function test_user_can_upload_profile_photo()
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $filePath = 'profile-photos/' . $user->id . '/dummy.jpg';

        $this->actingAs($user)
            ->postJson(route('api.v1.profile-photo.store'), ['file_path' => $filePath])
            ->assertOk()
            ->assertJson(['message' => 'Profile photo uploaded successfully.']);

        $this->assertEquals($filePath, $user->fresh()->userProfile->profile_photo_path);
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
