<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monapps</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="text-center w-full max-w-md">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4">Monapps</h1>
        <p class="text-gray-600 mb-8">Selamat datang di aplikasi Monapps</p>

        @guest
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                <a href="/login" class="w-full sm:w-auto text-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Login</a>
            </div>
        @endguest

        @auth
            <a href="/dashboard" class="inline-block w-full sm:w-auto px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Dashboard</a>
        @endauth
    </div>
</body>
</html>