<?php

namespace App\Http\Controllers;

use App\Imports\CoursesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        Excel::import(new CoursesImport, $request->file('file'));

        return redirect()->back()->with('success', 'Golf courses imported successfully.');
    }
}
