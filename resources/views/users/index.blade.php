@extends('layouts.app')
@section('content')
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('errors'))
                        <div class="bg-red-100 shadow sm:rounded-lg">
                            <ul class="list-disc ms-5">
                                @foreach (session('errors')->all() as $error)
                                    <li class="text-sm text-red-600">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status')))
                        <div class="bg-green-100 shadow sm:rounded-lg">
                            <p class="text-sm text-green-600">{{ session('status') }}</p>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 text-red-700 px-4 py-3">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Пользователи</h3>
                            <a href="{{ route('users.create') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <!-- плюсик -->
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M12 5a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H6a1 1 0 110-2h5V6a1 1 0 011-1z" />
                                </svg>
                                Добавить
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Имя</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Логин</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Роль</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse ($users as $user)
                                        @php
                                            $role = $user->role ?? '—';
                                            $roleClasses = match ($role) {
                                                'admin' => 'bg-red-100 text-red-800 ring-red-200',
                                                'manager' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                                                'editor' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                                'user' => 'bg-gray-100 text-gray-800 ring-gray-200',
                                                default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->id }}</td>
                                            <td class="px-6 py-4  text-sm text-gray-900">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-700 font-semibold">
                                                        {{ mb_strtoupper(mb_substr($user->name ?? $user->login, 0, 1)) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-medium text-gray-900 ">{{ $user->name ?? '—' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $user->login ?: '—' }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $roleClasses }}">
                                                    {{ $role }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                <div class="inline-flex items-center gap-2">

                                                    <a href="{{ route('users.edit', $user) }}"
                                                        class="px-2.5 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                                                        Редактировать
                                                    </a>
                                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                        onsubmit="return confirm('Удалить пользователя #{{ $user->id }} ({{ $user->login ?? $user->name }})? Это действие нельзя отменить.');"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="px-2.5 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">
                                                            Удалить
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                                Пока нет пользователей.
                                                <a href="{{ route('users.create') }}"
                                                    class="text-indigo-600 hover:underline">Создать первого</a>.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4">
                            {{ $users->onEachSide(1)->links('pagination::tailwind') }}
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection