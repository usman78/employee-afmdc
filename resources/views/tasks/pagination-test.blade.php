<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pagination Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="/css/app.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body class="bg-gray-100 text-gray-900">

    <div class="container mx-auto max-w-xl mt-10">
        <h1 class="text-2xl font-bold mb-4">Users List</h1>
        
        @foreach ($users as $user)
            <div class="border p-4 mb-2 bg-blue-500 shadow-sm rounded-md">
                {{ $user->name }}
            </div>
        @endforeach

        <div class="mt-6 flex justify-center">
            {{ $users->links() }}
        </div>
    </div>

</body>
</html>