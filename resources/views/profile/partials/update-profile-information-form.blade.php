@php($user = auth()->user())
<header>
    <h2 class="text-lg font-medium text-gray-900">Обновить данные профиля</h2>
    <p class="mt-1 text-sm text-gray-600">Измени отображаемое имя и логин. Логин используется для входа.</p>
</header>

<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('patch')

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
        <input id="name" name="name" type="text"
               value="{{ old('name', $user->name) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="login" class="block text-sm font-medium text-gray-700">Логин</label>
        <input id="login" name="login" type="text"
               value="{{ old('login', $user->login ?? '') }}"
               autocomplete="username"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('login')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-4">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            Сохранить
        </button>

        @if (session('status') === 'profile-updated')
            <p class="text-sm text-gray-600">Сохранено.</p>
        @endif
    </div>
</form>
