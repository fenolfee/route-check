<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Файлы - КЕМОНБ')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Top brand bar -->
        <header class="relative">
            <div class="absolute inset-0 "></div>
            <div class="relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex items-center justify-center">
                        <a href="{{ url('/') }}" class="text-white hover:opacity-90 transition">
                            @include('partials.brand')
                        </a>
                    </div>
                </div>
            </div>
            <!-- subtle wave divider -->
            <svg class="text-gray-50" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
                <path fill="currentColor"
                    d="M0,32L60,42.7C120,53,240,75,360,80C480,85,600,75,720,64C840,53,960,43,1080,37.3C1200,32,1320,32,1380,32L1440,32L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z" />
            </svg>
        </header>


        <!-- Page content -->
        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                @yield('content')
            </div>
        </main>


        <footer class="py-6 text-center text-sm text-gray-500">© {{ date('Y') }} — ГАУК ГНБК им. В.Д. Федорова</footer>
    </div>
</body>

</html>