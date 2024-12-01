<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CoursesImport;
use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ImportCourses extends Command
{
    protected $signature = 'import:courses {file}';
    protected $description = 'Import golf courses from an XLSX file and fetch geocoding data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error('File not found!');
            return;
        }

        // Import courses from the file
        Excel::import(new CoursesImport, $file);
        $this->info('Courses imported successfully.');

        // Fetch geocoding data for each course
        $courses = Course::whereNull('latitude')->orWhereNull('longitude')->get();

        foreach ($courses as $course) {
            $this->info("Geocoding: {$course->course_name}, {$course->city_name}");
            $this->geocodeCourse($course);
        }

        $this->info('Geocoding completed.');
    }

    private function geocodeCourse(Course $course)
    {
        try {
            // Try LocationIQ first
            $response = $this->geocodeWithLocationIQ($course);
            if ($response) {
                $this->updateCourse($course, $response);
                return;
            }

            // Try OpenCage if LocationIQ fails
            $response = $this->geocodeWithOpenCage($course);
            if ($response) {
                $this->updateCourse($course, $response);
                return;
            }

            // Try OpenStreetMap if both LocationIQ and OpenCage fail
            $response = $this->geocodeWithOpenStreetMap($course);
            if ($response) {
                $this->updateCourse($course, $response);
                return;
            }

            $this->warn("No results found for: {$course->course_name}");
        } catch (\Exception $e) {
            $this->error("Geocoding failed for: {$course->course_name} ({$e->getMessage()})");
        }
    }

    private function geocodeWithLocationIQ(Course $course)
    {
        $query = "{$course->course_name}, {$course->city_name}, Canada";
        $response = Http::withoutVerifying()->get('https://us1.locationiq.com/v1/search.php', [
            'key' => env('LOCATIONIQ_API_KEY'),
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
        ]);

        if ($response->successful() && !empty($response->json())) {
            $data = $response->json()[0];
            if (isset($data['lat']) && isset($data['lon'])) {
                return $data; // Return the data only if lat and lon are present
            }
        }

        return null;
    }

    private function geocodeWithOpenCage(Course $course)
    {
        $query = "{$course->course_name}, {$course->city_name}, Canada";
        $response = Http::withoutVerifying()->get('https://api.opencagedata.com/geocode/v1/json', [
            'q' => $query,
            'key' => env('OPENCAGE_API_KEY'),
        ]);

        if ($response->successful() && !empty($response->json()['results'])) {
            $data = $response->json()['results'][0];
            if (isset($data['geometry']['lat']) && isset($data['geometry']['lng'])) {
                return [
                    'lat' => $data['geometry']['lat'],
                    'lon' => $data['geometry']['lng'],
                    'display_name' => $data['formatted'], // You can choose the correct postal info
                ];
            }
        }

        return null;
    }

    private function geocodeWithOpenStreetMap(Course $course)
    {
        $query = "{$course->course_name}, {$course->city_name}, Canada";

        // Rate limit OpenStreetMap (1 request per second)
        $cacheKey = "osm_request_{$course->course_name}_{$course->city_name}";
        if (Cache::has($cacheKey)) {
            sleep(1); // Ensure we don't exceed the rate limit
        }

        $response = Http::withoutVerifying()->get('https://nominatim.openstreetmap.org/search', [
            'q' => $query,
            'format' => 'json',
        ]);

        // Store the request timestamp in cache to ensure rate-limiting
        Cache::put($cacheKey, now(), now()->addSecond());

        if ($response->successful() && !empty($response->json())) {
            $data = $response->json()[0];
            if (isset($data['lat']) && isset($data['lon'])) {
                return $data; // Return the data if lat and lon are present
            }
        }

        return null;
    }

    private function updateCourse(Course $course, $response)
    {
        if (isset($response['lat'], $response['lon'])) {
            $course->update([
                'latitude' => $response['lat'],
                'longitude' => $response['lon'],
                'postal_code' => $response['display_name'], // Postal code may not always be structured
            ]);

            $this->info("Updated: {$course->course_name} ({$course->latitude}, {$course->longitude}, {$course->postal_code})");
        } else {
            $this->warn("Geocoding data missing for: {$course->course_name}");
        }
    }
}
