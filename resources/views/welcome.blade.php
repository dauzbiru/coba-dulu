<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARS — Monitoring Assessment and Reporting System</title>
    <link rel="icon" type="image/png" href="/images/biru-favicon.png?v=5">
    <link rel="shortcut icon" href="/favicon.ico?v=5">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="text-center w-full max-w-md">
        <img src="/images/logo.png" alt="MARS" class="h-16 w-auto mx-auto mb-4">
        <p class="text-gray-600 mb-8">Selamat datang di Monitoring Assessment and Reporting System</p>

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