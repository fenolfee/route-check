<nav x-data="{ open: false, userMenu: false }" class="bg-white border-b border-gray-100">
    <!-- Верхняя панель -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Лого -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <!-- Простое SVG-лого, при желании замени на своё -->
                    <svg class="h-9 w-9 text-indigo-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 17.93V20h-2v-.07A8.006 8.006 0 0 1 4.07 13H4v-2h.07A8.006 8.006 0 0 1 11 4.07V4h2v.07A8.006 8.006 0 0 1 19.93 11H20v2h-.07A8.006 8.006 0 0 1 13 19.93Z"/>
                    </svg>
                    <span class="text-lg font-bold text-gray-800">Управление</span>
                </a>

                <!-- Навигация -->
                <div class="hidden sm:flex sm:items-center sm:ml-10 space-x-8">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                       {{ request()->routeIs('dashboard')
                            ? 'border-indigo-500 text-gray-900'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Главная
                    </a>
                    <a href="{{ route('users.index') }}"
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                       {{ request()->routeIs('users.index')
                            ? 'border-indigo-500 text-gray-900'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Пользователи
                    </a>
                    <a href="{{ route('files.index') }}"
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                       {{ request()->routeIs('files.index')
                            ? 'border-indigo-500 text-gray-900'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Файлы
                    </a>
                    {{-- Добавляй ссылки по аналогии --}}
                    {{-- <a href="{{ route('something') }}" class="...">Раздел</a> --}}
                </div>
            </div>

            <!-- Дропдаун пользователя -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 relative">
                <button type="button"
                        @click="userMenu = !userMenu"
                        @keydown.escape.window="userMenu=false"
                        class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition">
                    <span class="truncate max-w-[160px]">{{ Auth::user()->name }}</span>
                    <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.24 4.39a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Меню -->
                <div x-cloak x-show="userMenu" @click.outside="userMenu=false"
                     x-transition
                     class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md ring-1 ring-black/5 overflow-hidden z-50">
                    <a href="{{ route('profile.edit') }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Профиль</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Выход
                        </button>
                    </form>
                </div>
            </div>

            <!-- Бургер на мобилке -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Мобильное меню -->
    <div x-cloak :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium
               {{ request()->routeIs('dashboard')
                    ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                    : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                Главная
            </a>
        </div>

        <!-- Пользователь -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->login ?? Auth::user()->email }}
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}"
                   class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">
                    Профиль
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-left block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">
                        Выход
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
