<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_valid_file()
    {
        // Mock the Excel import functionality
        Excel::fake();

        // Create a fake Excel file
        $file = UploadedFile::fake()->create('courses.xlsx', 100);

        // Send a POST request to the import method with the fake file
        $response = $this->post(route('courses.import'), [
            'file' => $file,
        ]);

        // Assert that the Excel import was called
        Excel::assertImported('courses.xlsx');

        // Assert redirect response with success message
        $response->assertRedirect()->with('success', 'Golf courses imported successfully.');
    }

    public function test_import_invalid_file_type()
    {
        // Create a fake text file (invalid type)
        $file = UploadedFile::fake()->create('courses.txt', 100);

        // Send a POST request to the import method with the invalid file
        $response = $this->post(route('courses.import'), [
            'file' => $file,
        ]);

        // Assert validation error response
        $response->assertSessionHasErrors('file');
    }

    public function test_import_no_file()
    {
        // Send a POST request to the import method without a file
        $response = $this->post(route('courses.import'));

        // Assert validation error response
        $response->assertSessionHasErrors('file');
    }
}
