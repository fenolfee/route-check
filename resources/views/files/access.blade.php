@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold mb-6">Настройки доступа</h1>

        @if (session('success'))
            <div class="mb-4 rounded bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 text-sm text-gray-600">
            Папка: <code class="bg-gray-200 px-2 py-0.5 rounded">/{{ $rel }}</code>
        </div>

        <form method="POST" action="{{ route('files.access.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="path" value="{{ $rel }}" />

            <fieldset class="space-y-3">
                <legend class="text-sm font-medium text-gray-700 mb-2">Режим доступа</legend>

                <label class="flex items-center gap-3">
                    <input type="radio" name="access" value="open" @checked(old('access', $rule->access) === 'open')>
                    <span>Открытая директория (доступ всем)</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="radio" name="access" value="trusted" @checked(old('access', $rule->access) === 'trusted')>
                    <span>Открыта из доверенных подсетей; из остальных — только по логину
                        <br> старше 50 лет открыты (если последняя папка это год)
                    </span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="radio" name="access" value="closed" @checked(old('access', $rule->access) === 'closed')>
                    <span>Закрытая (доступ запрещён всем, код 403)</span>
                </label>

                @error('access')
                    <div class="text-sm text-red-600">{{ $message }}</div>
                @enderror
            </fieldset>

            <div>
                <label for="trusted_subnets" class="block text-sm font-medium text-gray-700">
                    Доверенные подсети (CIDR), по одной в строке или через запятую
                </label>
                <textarea id="trusted_subnets" name="trusted_subnets" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="192.168.0.0/16
    10.0.0.0/8">{{ old('trusted_subnets', implode("\n", $rule->trusted_subnets ?? [])) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Пример: 192.168.1.0/24, 10.0.0.0/8</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                    Сохранить
                </button>
                <a href="{{ route('files.index', ['path' => $rel]) }}" class="text-gray-600 hover:underline">Отмена</a>
            </div>
        </form>
    </div>
@endsection