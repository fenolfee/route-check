<nav x-data="{ open: false, userMenu: false }" class="relative">
    <!-- Градиентная шапка -->
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-500"></div>

    <!-- Контент шапки -->
    <div class="relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Лого + название -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-white hover:opacity-90">
                        <img src="{{ asset('images/logo.svg') }}" alt="Логотип" class="h-10 w-auto">
                        <div class="hidden sm:flex flex-col leading-tight">
                            <span class="text-lg font-semibold">Управление</span>
                            <span class="text-xs opacity-80">ГАУК ГНБК им. В.Д. Федорова</span>
                        </div>
                    </a>

                    <!-- Навигация -->
                    <div class="hidden sm:flex sm:items-center sm:ml-10 gap-6">
                        <a href="{{ route('dashboard') }}"
                            class="text-sm font-medium text-white {{ request()->routeIs('dashboard') ? 'underline decoration-2 underline-offset-4' : 'hover:opacity-90' }}">
                            Главная
                        </a>
                        <a href="{{ route('users.index') }}"
                            class="text-sm font-medium text-white {{ request()->routeIs('users.index') ? 'underline decoration-2 underline-offset-4' : 'hover:opacity-90' }}">
                            Пользователи
                        </a>
                        <a href="{{ route('files.index') }}"
                            class="text-sm font-medium text-white {{ request()->routeIs('files.index') ? 'underline decoration-2 underline-offset-4' : 'hover:opacity-90' }}">
                            Файлы
                        </a>
                        <a href="{{ route('admin.trusted-subnets.index') }}"
                            class="text-sm font-medium text-white {{ request()->routeIs('admin.trusted-subnets.index') ? 'underline decoration-2 underline-offset-4' : 'hover:opacity-90' }}">
                            Доверенные подсети
                        </a>
                        {{-- Добавляй ссылки по аналогии --}}
                    </div>
                </div>

                <!-- Дропдаун пользователя -->
                <div class="hidden sm:flex sm:items-center sm:ml-6 relative">
                    <button type="button" @click="userMenu = !userMenu" @keydown.escape.window="userMenu=false"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md text-white/90 bg-white/10 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/40">
                        <span class="truncate max-w-[160px]">{{ Auth::user()->name }}</span>
                        <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.24 4.39a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Меню -->
                    <div x-cloak x-show="userMenu" @click.outside="userMenu=false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white shadow-xl rounded-xl ring-1 ring-black/10 overflow-hidden z-50">
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
                        class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/40">
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
    </div>

    <!-- Волнушка-разделитель под шапкой -->
    <svg class="text-gray-50" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
        <path fill="currentColor"
            d="M0,32L60,42.7C120,53,240,75,360,80C480,85,600,75,720,64C840,53,960,43,1080,37.3C1200,32,1320,32,1380,32L1440,32L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z" />
    </svg>

    <!-- Мобильное меню -->
    <div x-cloak :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                Главная
            </a>
            <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                Пользователи
            </a>
            <a href="{{ route('files.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                Файлы
            </a>
            <a href="{{ route('admin.trusted-subnets.index') }}"
                class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                Доверенные подсети
            </a>

            <!-- Пользователь -->
            <div class="pt-3 border-t border-gray-100">
                <div class="px-3 text-sm text-gray-500">{{ Auth::user()->name }}</div>
                <div class="px-3">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">Профиль</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                            Выход
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>