<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;

class CoursesImport implements ToModel
{
    private $courseName = null; // Holds the current course name
    private $cityName = null; // Holds the current city name
    private $rowCount = 0; // Tracks the current row index

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): ?Course
    {
        // Increment row count
        $this->rowCount++;

        // Check if the current row is odd or even
        if ($this->rowCount % 2 === 1) {
            // Odd row: this should be the course name
            $this->courseName = isset($row[0]) ? $row[0] : null;

            // Return null; we need to wait for the next row
            return null;
        } else {
            // Even row: this should be the city name
            $cityName = isset($row[0]) ? $row[0] : null;

            // Ensure both course name and city name are set before creating the model
            if ($this->courseName && $cityName) {
                $Course = new Course([
                    'course_name' => $this->courseName,
                    'city_name' => $cityName,
                ]);

                // Reset course name for the next pair of rows
                $this->courseName = null;

                return $Course;
            } else {
                // If course name or city name is null, return null
                return null;
            }
        }
    }
}
