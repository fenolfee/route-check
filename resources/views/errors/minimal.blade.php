<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ошибка') — {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-lg text-center">
            <div class="mb-6 flex justify-center">
                @include('partials.brand')
            </div>
            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-black/5 p-8">
                <div class="text-7xl font-black text-indigo-600 leading-none">@yield('code')</div>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">@yield('headline')</h1>
                <p class="mt-3 text-gray-600">@yield('message')</p>
                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                  
                </div>
            </div>
            <p class="mt-6 text-xs text-gray-400">Код: @yield('code') • {{ now()->format('d.m.Y H:i') }}</p>
        </div>
    </div>
</body>

</html>