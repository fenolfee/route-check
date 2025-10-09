@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Флеш-сообщения --}}
        @if (session('success') || session('error'))
            <div class="mb-4">
                @if (session('success'))
                    <div class="rounded-md bg-green-50 text-green-800 px-4 py-3">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-md bg-red-50 text-red-700 px-4 py-3">{{ session('error') }}</div>
                @endif
            </div>
        @endif

        <div class="mb-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">Файловый менеджер</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        База:
                        <code class="bg-gray-100 px-2 py-0.5 rounded">{{ $basePath }}</code>
                    </p>
                </div>
                {{-- опционально: ссылка на глобальные подсети --}}
                <a href="{{ route('admin.trusted-subnets.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-md border bg-white hover:bg-gray-50 text-sm">
                    Глобальные подсети
                </a>
            </div>

            {{-- Легенда по доступу --}}
            <p class="mt-3 text-xs text-gray-500 flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full ring-1 bg-green-100 text-green-800 ring-green-200">Открыто</span>
                — доступ всем
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full ring-1 bg-amber-100 text-amber-800 ring-amber-200">Доверенные</span>
                — из доверенных подсетей без логина, из остальных — по логину
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full ring-1 bg-red-100 text-red-800 ring-red-200">Закрыто</span>
                — никому (403)
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
                            class="hover:text-indigo-600">
                            {{ $crumb['label'] }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-600 truncate">
                    Текущий каталог:
                    <span class="font-medium">{{ $currentRel === '' ? 'Корень' : $currentRel }}</span>
                </div>
                @if($currentRel !== '')
                    @php $parent = implode('/', array_slice(explode('/', $currentRel), 0, -1)); @endphp
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Имя
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                Размер</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                Изменён</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-64">Доступ</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($items as $item)

                            @php
                                $access = $item['access'] ?? null; // open|trusted|closed
                                $badgeClass = match ($access) {
                                    'open' => 'bg-green-100 text-green-800 ring-green-200',
                                    'trusted' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                    'closed' => 'bg-red-100 text-red-800 ring-red-200',
                                    default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                };
                                $label = match ($access) {
                                    'open' => 'Открыто',
                                    'trusted' => 'Доверенные',
                                    'closed' => 'Закрыто',
                                    default => $access,
                                };
                                $rulePath = $item['rule_path'] ?? '';
                                $inherited = !empty($item['inherited']);
                                $globCnt = $item['trusted_global_count'] ?? 0;
                                $dirCnt = $item['trusted_dir_count'] ?? 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                {{-- Имя --}}
                                <td class="px-6 py-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        @if($item['is_dir'])
                                            <svg class="h-5 w-5 text-amber-500" viewBox="0 0 24 24" fill="currentColor"
                                                aria-hidden="true">
                                                <path d="M10 4H4a2 2 0 00-2 2v1h20V8a2 2 0 00-2-2h-8l-2-2z" />
                                                <path d="M22 9H2v9a2 2 0 002 2h16a2 2 0 002-2V9z" />
                                            </svg>
                                            <a class="font-medium text-indigo-700 hover:underline"
                                                href="{{ route('files.index', ['dir' => $item['rel']]) }}">
                                                {{ $item['name'] }}
                                            </a>
                                        @else
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                                aria-hidden="true">
                                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                                <path d="M14 2v6h6" />
                                            </svg>
                                            <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Размер --}}
                                <td class="px-6 py-3 text-sm text-gray-600 hidden md:table-cell">
                                    @if($item['is_dir'])
                                        —
                                    @else
                                        {{ $item['size'] !== null ? number_format($item['size'] / 1024, 1, ',', ' ') . ' КБ' : '—' }}
                                    @endif
                                </td>

                                {{-- Изменён --}}
                                <td class="px-6 py-3 text-sm text-gray-600 hidden md:table-cell">
                                    {{ $item['mtime'] ? \Carbon\Carbon::createFromTimestamp($item['mtime'])->format('d.m.Y H:i') : '—' }}
                                </td>

                                {{-- Доступ --}}
                                <td class="px-6 py-3 text-sm">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $badgeClass }}">
                                            {{ $label }}
                                        </span>

                                        {{-- унаследовано --}}
                                        @if($access && $inherited)
                                            <span class="text-xs text-gray-500" title="Правило унаследовано">
                                                ← из {{ $rulePath !== '' ? $rulePath : 'корня' }}
                                            </span>
                                        @endif

                                        {{-- счётчики для trusted --}}
                                        @if($access === 'trusted')
                                            <span class="text-[11px] text-gray-500">
                                                подсети: глоб {{ $globCnt }} + лок {{ $dirCnt }}
                                            </span>
                                        @endif
                                        @if(!empty($item['historic_open']))
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 ring-1 ring-blue-200">
                                                открыт по истечению {{ config('filebrowser.historic.years') }}
                                            </span>
                                        @endif

                                    </div>
                                </td>

                                {{-- Действия --}}
                                <td class="px-6 py-3 text-sm text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if($item['is_dir'])
                                            <a href="{{ route('files.index', ['dir' => $item['rel']]) }}"
                                                class="px-2.5 py-1.5 rounded-md border hover:bg-gray-50">
                                                Открыть
                                            </a>
                                            <a href="{{ route('files.access.edit', ['path' => $item['rel']]) }}"
                                                class="px-2.5 py-1.5 rounded-md border bg-white hover:bg-gray-50">
                                                Доступ
                                            </a>
                                        @else
                                            <a href="{{ route('files.download', ['file' => $item['rel']]) }}"
                                                class="px-2.5 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                                                Скачать
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
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