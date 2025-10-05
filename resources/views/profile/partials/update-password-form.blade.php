<header>
    <h2 class="text-lg font-medium text-gray-900">Сменить пароль</h2>
    <p class="mt-1 text-sm text-gray-600">Придумай надёжный новый пароль.</p>
</header>

<form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('put')

    <div>
        <label for="current_password" class="block text-sm font-medium text-gray-700">Текущий пароль</label>
        <input id="current_password" name="current_password" type="password" autocomplete="current-password"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('current_password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Новый пароль</label>
        <input id="password" name="password" type="password" autocomplete="new-password"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Подтверждение пароля</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="flex items-center gap-4">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            Обновить пароль
        </button>

        @if (session('status') === 'password-updated')
            <p class="text-sm text-gray-600">Пароль обновлён.</p>
        @endif
    </div>
</form>
