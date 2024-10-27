<!-- resources/views/import.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Import Courses</title>
</head>
<body>
    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <form action="{{ route('courses.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Import Courses</button>
    </form>
</body>
</html>
