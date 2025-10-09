@extends('layouts.guest')
@section('title', 'Вход')
@section('content')
    <div class="w-full max-w-md mx-auto">
        @if (session('status'))
            <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
        @endif


        @if ($errors->any())
            <x-alert type="error" class="mb-4">
                <div class="font-semibold mb-1">Проверьте введённые данные</div>
                <ul class="list-disc ms-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif


        <form method="POST" action="{{ route('login') }}" class="bg-white rounded-2xl shadow-lg ring-1 ring-black/5 p-6">
            @csrf
            <div>
                <label for="login" class="block text-sm font-medium text-gray-700">Эл. почта или логин</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                    autocomplete="username"
                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                    placeholder="ваш логин">
                @error('login')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div class="mt-4 flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-700">Запомнить меня</span>
                </label>
                {{--
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Забыли
                        пароль?</a>
                @endif --}}
            </div>


            <div class="mt-6">
                <button type="submit"
                    class="w-full inline-flex justify-center items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-white font-semibold shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Войти</button>
@endsection