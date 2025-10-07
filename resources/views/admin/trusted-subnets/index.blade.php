@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Глобальные доверенные подсети</h1>

    {{-- флеши --}}
    @if (session('success'))
        <div class="mb-4 rounded bg-green-50 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3">
            <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    {{-- форма добавления --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <h2 class="text-lg font-semibold mb-3">Добавить подсеть</h2>
        <form method="POST" action="{{ route('admin.trusted-subnets.store') }}" class="grid gap-3 md:grid-cols-6">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-700 mb-1">CIDR</label>
                <input name="cidr" type="text" placeholder="192.168.0.0/16"
                       class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm text-gray-700 mb-1">Метка</label>
                <input name="label" type="text" placeholder="Офис / VPN"
                       class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-1 flex items-end">
                <button class="w-full px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                    Добавить
                </button>
            </div>
        </form>
    </div>

    {{-- таблица --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">CIDR</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Метка</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Статус</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($subnets as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.trusted-subnets.update', $row) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input name="cidr" value="{{ $row->cidr }}"
                                           class="w-48 md:w-64 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <input type="hidden" name="label" value="{{ $row->label }}">
                                    <input type="hidden" name="is_enabled" value="{{ $row->is_enabled ? 1 : 0 }}">
                                    <button class="px-3 py-1.5 rounded-md border text-sm hover:bg-gray-50">Сохранить</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.trusted-subnets.update', $row) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="cidr" value="{{ $row->cidr }}">
                                    <input name="label" value="{{ $row->label }}" placeholder="Описание"
                                           class="w-56 md:w-80 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <input type="hidden" name="is_enabled" value="{{ $row->is_enabled ? 1 : 0 }}">
                                    <button class="px-3 py-1.5 rounded-md border text-sm hover:bg-gray-50">Сохранить</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.trusted-subnets.update', $row) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="cidr" value="{{ $row->cidr }}">
                                    <input type="hidden" name="label" value="{{ $row->label }}">
                                    <input type="hidden" name="is_enabled" value="{{ $row->is_enabled ? 0 : 1 }}">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $row->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $row->is_enabled ? 'Включена' : 'Выключена' }}
                                    </span>
                                    <button class="ml-3 px-3 py-1.5 rounded-md text-sm
                                                   {{ $row->is_enabled ? 'bg-yellow-100 text-yellow-900 hover:bg-yellow-200' : 'bg-green-100 text-green-900 hover:bg-green-200' }}">
                                        {{ $row->is_enabled ? 'Выключить' : 'Включить' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.trusted-subnets.destroy', $row) }}"
                                      onsubmit="return confirm('Удалить {{ $row->cidr }}?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-md bg-red-600 text-white text-sm hover:bg-red-700">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">Пока пусто.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $subnets->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection