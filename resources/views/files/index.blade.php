@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold">Файловый менеджер</h1>
            <p class="text-sm text-gray-600">База: <code class="bg-gray-100 px-2 py-0.5 rounded">{{ $basePath }}</code>
            </p>
        </div>

        {{-- Хлебные крошки --}}
        <nav class="mb-4 text-sm text-gray-600">
            <ol class="flex items-center flex-wrap gap-1">
                @foreach($breadcrumbs as $i => $crumb)
                    @if($i > 0)
                        <li class="px-1 text-gray-400">/</li>
                    @endif
                    <li>
                        <a href="{{ route('files.index', $crumb['rel'] === '' ? [] : ['dir' => $crumb['rel']]) }}"
                            class="hover:text-indigo-600">{{ $crumb['label'] }}</a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-600 truncate">
                    Текущий каталог: <span class="font-medium">{{ $currentRel === '' ? 'Корень' : $currentRel }}</span>
                </div>
                @if($currentRel !== '')
                    @php
                        $parent = implode('/', array_slice(explode('/', $currentRel), 0, -1));
                    @endphp
                    <a href="{{ route('files.index', $parent === '' ? [] : ['dir' => $parent]) }}"
                        class="text-sm inline-flex items-center gap-2 px-3 py-1.5 rounded-md border hover:bg-gray-50">
                        ↑ Вверх
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Имя</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                Размер</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                Изменён</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        @if($item['is_dir'])
                                            <svg class="h-5 w-5 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M10 4H4a2 2 0 00-2 2v1h20V8a2 2 0 00-2-2h-8l-2-2z" />
                                                <path d="M22 9H2v9a2 2 0 002 2h16a2 2 0 002-2V9z" />
                                            </svg>
                                            <a class="font-medium text-indigo-700 hover:underline"
                                                href="{{ route('files.index', ['dir' => $item['rel']]) }}">
                                                {{ $item['name'] }}
                                            </a>
                                        @else
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                                <path d="M14 2v6h6" />
                                            </svg>
                                            <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600 hidden md:table-cell">
                                    @if($item['is_dir'])
                                        —
                                    @else
                                        {{ $item['size'] !== null ? number_format($item['size'] / 1024, 1, ',', ' ') . ' КБ' : '—' }}
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600 hidden md:table-cell">
                                    {{ $item['mtime'] ? \Carbon\Carbon::createFromTimestamp($item['mtime'])->format('d.m.Y H:i') : '—' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-right">
                                    @if($item['is_dir'])
                                        <a href="{{ route('files.index', ['dir' => $item['rel']]) }}"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-md border hover:bg-gray-50">
                                            Открыть
                                        </a>
                                        <a href="{{ route('files.access.edit', ['path' => $item['rel']]) }}"
                                            class="px-2.5 py-1.5 rounded-md border bg-white hover:bg-gray-50">Доступ</a>

                                    @else
                                        <a href="{{ route('files.download', ['file' => $item['rel']]) }}"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                                            Скачать
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Пусто.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection