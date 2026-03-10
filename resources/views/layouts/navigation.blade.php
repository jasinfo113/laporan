<nav class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between p-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse h-16">
            <x-application-logo class="h-8 w-8 fill-current text-gray-800 dark:text-gray-100" />
            {{-- <span class="self-center whitespace-nowrap text-xl font-semibold text-gray-900 dark:text-white">{{ config('app.name', 'Daily Task') }}</span> --}}
        </a>

        <div class="flex items-center gap-3 md:order-2">
            <button type="button" data-theme-toggle class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                <svg data-theme-toggle-icon-dark class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm4 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-.464 4.95.707.707a1 1 0 0 1-1.414 1.414l-.707-.707a1 1 0 0 1 1.414-1.414Zm2.12-10.607a1 1 0 0 0-1.414 0l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707a1 1 0 0 0 0-1.414ZM17 11a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2h-1Zm-7 6a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm-4.95-2.464a1 1 0 0 0 0 1.414l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707a1 1 0 0 0-1.414-1.414ZM4 10a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm.343-5.657a1 1 0 0 1 1.414 0l.707.707A1 1 0 0 1 5.05 6.464l-.707-.707a1 1 0 0 1 0-1.414Z"/>
                </svg>
                <svg data-theme-toggle-icon-light class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                    <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a8.001 8.001 0 1 0 10.586 10.586Z"/>
                </svg>
            </button>

            <button id="user-menu-button" data-dropdown-toggle="user-dropdown" type="button" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                <span class="me-2">{{ Auth::user()->name }}</span>
                <svg class="h-2.5 w-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                </svg>
            </button>

            <div id="user-dropdown" class="z-50 hidden w-44 divide-y divide-gray-100 rounded-lg bg-white text-base shadow-sm dark:divide-gray-600 dark:bg-gray-700">
                <div class="px-4 py-3">
                    <span class="block truncate text-sm text-gray-500 dark:text-gray-300">{{ Auth::user()->email }}</span>
                </div>
                <ul class="py-2" aria-labelledby="user-menu-button">
                    <li>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">Profile</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">Log Out</button>
                        </form>
                    </li>
                </ul>
            </div>

            <button data-collapse-toggle="navbar-menu" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-sm text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600 md:hidden" aria-controls="navbar-menu" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>
        </div>

        <div id="navbar-menu" class="hidden w-full md:order-1 md:block md:w-auto">
            <ul class="mt-4 flex flex-col rounded-lg border border-gray-100 bg-gray-50 p-4 font-medium md:mt-0 md:flex-row md:space-x-1 md:border-0 md:bg-white md:p-0 rtl:space-x-reverse dark:border-transparent dark:bg-transparent">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Dashboard</a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Laporan Bulanan</a>
                </li>
                <li>
                    <a href="{{ route('leaves.index') }}" class="{{ request()->routeIs('leaves.*') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Cuti Tahunan</a>
                </li>

                @if(Auth::user()->role === 'admin')
                    <li>
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Master Pegawai</a>
                    </li>
                    <li>
                        <a href="{{ route('job_packages.index') }}" class="{{ request()->routeIs('job_packages.*') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Master Pekerjaan & Scope</a>
                    </li>
                    <li>
                        <a href="{{ route('approvers.index') }}" class="{{ request()->routeIs('approvers.*') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Master Pejabat</a>
                    </li>
                    <li>
                        <a href="{{ route('contracts.index') }}" class="{{ request()->routeIs('contracts.*') ? 'bg-blue-700 text-white md:bg-blue-50 md:text-blue-700 dark:md:bg-blue-900/40 dark:md:text-blue-300' : 'text-gray-900 hover:bg-gray-100 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-400' }} block rounded-lg px-3 py-2">Manajemen Kontrak</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
