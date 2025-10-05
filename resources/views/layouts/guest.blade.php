<!DOCTYPE html>
<html lang="ru" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Вход')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-6">
        @yield('content')
    </div>
</body>

</html>