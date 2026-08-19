<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KDP Author Manager - Panel Administrativo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.classList.toggle('dark', localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches));
    </script>
</head>
<body class="bg-slate-50">
    <div class="flex h-screen bg-white">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-slate-900 text-white flex flex-col fixed left-0 top-0 h-full shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40">
            <!-- Logo/Brand -->
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold text-white">KDP Author Manager</h1>
                <p class="text-xs text-slate-400 mt-1">Panel Administrativo</p>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-4m0 0l7-4 7 4M5 8v10a1 1 0 001 1h12a1 1 0 001-1V8M5 8l7-4 7 4"/></svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <div class="pt-2">
                    <p class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Gestión de Contenido</p>
                </div>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747 0-6.002-4.5-10.747-10-10.747z"/></svg>
                    <span>Obras</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Ediciones</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Publicaciones</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Regalías</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16a1 1 0 001 1h8a1 1 0 001-1V4m0 0L7 4m8 0l4-3m-4 3v2m0 0h5m-5 0a2 2 0 100-4H7a2 2 0 100 4"/></svg>
                    <span>Promociones</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Distribución</span>
                </a>

                <div class="pt-2">
                    <p class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Herramientas</p>
                </div>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>IA/OCR</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span>Fuentes</span>
                </a>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Tareas</span>
                </a>

                <div class="pt-2">
                    <p class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Sistema</p>
                </div>

                <a href="#" class="nav-link group flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-slate-300 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zM5 20h16a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Usuarios</span>
                </a>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-700">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-white">MP</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">Administrador</p>
                        <p class="text-xs text-slate-400 truncate">admin@kdp.local</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64 w-full lg:w-auto">
            <!-- Top Header -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4">
                    <!-- Mobile Menu Toggle -->
                    <button id="menu-toggle" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Header Title/Breadcrumb Area -->
                    <div class="flex-1 ml-4 lg:ml-0">
                        <h2 class="text-2xl font-bold text-slate-900">{{ $pageTitle ?? 'Panel Administrativo' }}</h2>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center gap-4">
                        <button class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </button>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <div class="relative group">
                            <button class="flex items-center gap-2 text-slate-700 hover:text-slate-900 transition-colors">
                                <img src="https://ui-avatars.com/api/?name=Admin+User" alt="Profile" class="w-8 h-8 rounded-full">
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Breadcrumb -->
                @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                <div class="px-4 sm:px-6 py-2 bg-slate-50 border-t border-slate-200">
                    <nav class="flex items-center space-x-2 text-sm">
                        @foreach($breadcrumbs as $index => $crumb)
                            @if($index < count($breadcrumbs) - 1)
                                <a href="{{ $crumb['url'] }}" class="text-blue-600 hover:text-blue-800">{{ $crumb['label'] }}</a>
                                <span class="text-slate-400">/</span>
                            @else
                                <span class="text-slate-600">{{ $crumb['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                </div>
                @endif
            </header>

            <!-- Alerts -->
            <div class="px-4 sm:px-6 py-4 space-y-3">
                @if(session('success'))
                    <div class="flex items-start gap-4 p-4 bg-green-50 border border-green-200 rounded-lg animate-in fade-in slide-in-from-top">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-green-800">¡Éxito!</h3>
                            <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.closest('.animate-in').remove()" class="flex-shrink-0 text-green-600 hover:text-green-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="flex items-start gap-4 p-4 bg-red-50 border border-red-200 rounded-lg animate-in fade-in slide-in-from-top">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-800">¡Error!</h3>
                            <ul class="text-sm text-red-700 mt-2 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button onclick="this.closest('.animate-in').remove()" class="flex-shrink-0 text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-auto">
                <div class="px-4 sm:px-6 py-6">
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                        <div class="p-6">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Menu Script -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });

            // Close sidebar when clicking on a nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        sidebar.classList.add('-translate-x-full');
                    }
                });
            });

            // Close sidebar when clicking outside (mobile)
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024 && 
                    !sidebar.contains(e.target) && 
                    !menuToggle.contains(e.target) &&
                    !sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    </script>
</body>
</html>
