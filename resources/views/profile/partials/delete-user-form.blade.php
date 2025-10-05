<header>
    <h2 class="text-lg font-medium text-gray-900">Удалить аккаунт</h2>
    <p class="mt-1 text-sm text-gray-600">
        Это действие нельзя отменить. Все данные пользователя будут удалены.
    </p>
</header>

<form method="post" action="{{ route('profile.destroy') }}" class="mt-6 space-y-6"
      onsubmit="return confirm('Точно удалить аккаунт? Это действие нельзя отменить.');">
    @csrf
    @method('delete')

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
            Подтверди пароль
        </label>
        <input id="password" name="password" type="password" autocomplete="current-password"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
        Удалить аккаунт
    </button>
</form>
