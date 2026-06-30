<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gadungGuard - Daftarkan Rendaman</title>
    
    <!-- Tailwind CSS (via Vite bundle, styled with Tailwind CSS v4) -->
    @vite(['resources/css/app.css'])

    <!-- Google Fonts: Instrument Sans (configured in Vite) & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0c1322;
        }
        /* Custom scrollbar for dark mode */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0c1322;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="text-slate-100 overflow-x-hidden antialiased">

    <!-- Main Container -->
    <div class="flex min-h-screen" x-data="rendamanApp()">
        
        <!-- Backdrop overlay untuk mobile -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-black/60 z-30 md:hidden"></div>

        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 w-64 bg-[#0f172a] border-r border-[#1e293b] flex flex-col justify-between p-6 shrink-0 z-40 md:relative md:translate-x-0 transition-transform duration-300 ease-in-out">
            <div>
                <!-- Logo / Brand & Close Button -->
                <div class="flex items-center justify-between mb-10">
                    <div class="flex items-center space-x-3">
                        <div class="bg-emerald-500/10 p-2 rounded-lg border border-emerald-500/30">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-wide bg-gradient-to-r from-emerald-400 to-teal-200 bg-clip-text text-transparent">gadungGuard</span>
                    </div>
                    <!-- Close button for mobile -->
                    <button @click="sidebarOpen = false" class="md:hidden p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Nav Menu -->
                <nav class="space-y-1.5">
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v5.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 013 18.375v-5.25zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125v-9.75zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v14.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <span>Metrics</span>
                    </a>
                    <a href="{{ route('process') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <span>Process</span>
                    </a>
                    <a href="{{ route('sensory') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Sensory</span>
                    </a>
                    <a href="{{ route('rendaman') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500/10 to-transparent text-emerald-400 border-l-2 border-emerald-500 font-medium transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Daftarkan Rendaman</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.936 6.936 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar Bottom Settings -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:text-rose-400 transition duration-200">
                <svg class="w-5 h-5 text-rose-500/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span>Logout</span>
            </a>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 bg-[#0c1322] p-8 flex flex-col space-y-6 overflow-y-auto">

            <!-- Top Navbar / Header -->
            <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div class="flex items-center">
                    <!-- Hamburger menu button for mobile -->
                    <button @click="sidebarOpen = true" class="md:hidden mr-3 p-2 rounded-xl bg-[#151f32] border border-[#233554] text-slate-300 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Daftarkan Rendaman Baru</h1>
                        <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Registrasi wadah perendaman gadung baru ke sistem monitoring</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end mt-2 sm:mt-0">
                    <!-- User Profile Card -->
                    <div class="flex items-center space-x-2 bg-[#151f32] px-2.5 sm:px-3.5 py-1.5 rounded-xl border border-[#233554]">
                        <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-500 flex items-center justify-center font-bold text-xs text-slate-900">
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <span class="hidden sm:inline text-xs sm:text-sm font-medium text-slate-200">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    </div>
                </div>
            </header>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- REGISTRATION FORM SECTION                             -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <section class="bg-[#151f32]/60 backdrop-blur-md border border-[#233554] rounded-2xl p-8 relative overflow-hidden">
                <!-- Background glow decorations -->
                <div class="absolute -top-16 -right-16 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Section Header -->
                <div class="flex items-center space-x-4 mb-8 relative z-10">
                    <div class="bg-emerald-500/10 p-3 rounded-xl border border-emerald-500/30">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-wide">Formulir Pendaftaran Rendaman</h2>
                        <p class="text-sm text-slate-400 mt-1">Isi detail proses perendaman gadung untuk mulai monitoring otomatis</p>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('devices.store') }}" method="POST" class="relative z-10 max-w-2xl">
                    @csrf
                    <input type="hidden" name="redirect_to" value="/">
                    <input type="hidden" name="sensor_mode" :value="sensorMode">

                    <div class="space-y-6">

                        <!-- Nomor Rendaman -->
                        <div>
                            <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Nomor rendaman</label>
                            <input 
                                type="text" 
                                value="{{ $nextDeviceCode }} (otomatis)" 
                                disabled 
                                class="w-full bg-[#0c1322]/60 border border-[#233554] px-4 py-3 rounded-xl text-slate-400 cursor-not-allowed text-sm focus:outline-none">
                        </div>

                        <!-- Metode Penghilangan Racun -->
                        <div>
                            <label for="detoxification_method" class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Metode penghilangan racun</label>
                            <div class="relative">
                                <select 
                                    id="detoxification_method"
                                    name="detoxification_method" 
                                    required
                                    class="w-full bg-[#0c1322]/60 border border-[#233554] text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none appearance-none cursor-pointer transition duration-200">
                                    <option value="Air garam" selected>Air garam</option>
                                    <option value="Air mengalir">Air mengalir</option>
                                    <option value="Abu dapur">Abu dapur</option>
                                    <option value="Air kapur">Air kapur</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- 2x2 Grid Numeric Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Konsentrasi -->
                            <div>
                                <label for="concentration" class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Konsentrasi (%)</label>
                                <input 
                                    type="number" 
                                    id="concentration"
                                    name="concentration" 
                                    step="0.1" 
                                    min="0" 
                                    max="100" 
                                    placeholder="3"
                                    class="w-full bg-[#0c1322]/60 border border-[#233554] text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition duration-200 placeholder-slate-600">
                            </div>

                            <!-- Ketebalan Potongan -->
                            <div>
                                <label for="slice_thickness" class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Ketebalan potongan (mm)</label>
                                <input 
                                    type="number" 
                                    id="slice_thickness"
                                    name="slice_thickness" 
                                    step="0.1" 
                                    min="0" 
                                    max="100" 
                                    placeholder="4"
                                    class="w-full bg-[#0c1322]/60 border border-[#233554] text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition duration-200 placeholder-slate-600">
                            </div>

                            <!-- Berat Gadung -->
                            <div>
                                <label for="yam_weight" class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Berat gadung (kg)</label>
                                <input 
                                    type="number" 
                                    id="yam_weight"
                                    name="yam_weight" 
                                    step="0.1" 
                                    min="0" 
                                    max="1000" 
                                    placeholder="2.0"
                                    class="w-full bg-[#0c1322]/60 border border-[#233554] text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition duration-200 placeholder-slate-600">
                            </div>

                            <!-- Volume Air -->
                            <div>
                                <label for="water_volume" class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Volume air (liter)</label>
                                <input 
                                    type="number" 
                                    id="water_volume"
                                    name="water_volume" 
                                    step="0.1" 
                                    min="0" 
                                    max="10000" 
                                    placeholder="4.5"
                                    class="w-full bg-[#0c1322]/60 border border-[#233554] text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition duration-200 placeholder-slate-600">
                            </div>
                        </div>

                        <!-- Mode Sensor -->
                        <div>
                            <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Mode sensor</label>
                            <div class="grid grid-cols-2 gap-4 max-w-md">
                                <!-- Button Menetap -->
                                <button 
                                    type="button" 
                                    @click="sensorMode = 'Menetap'" 
                                    :class="sensorMode === 'Menetap' ? 'bg-[#2563eb] text-white border-transparent shadow-lg shadow-blue-500/20' : 'bg-[#0c1322]/60 text-slate-400 border border-[#233554] hover:text-white hover:bg-slate-800/60'"
                                    class="py-3 rounded-xl text-sm font-semibold flex items-center justify-center space-x-2 transition duration-200">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                                    </svg>
                                    <span>Menetap</span>
                                </button>

                                <!-- Button Keliling -->
                                <button 
                                    type="button" 
                                    @click="sensorMode = 'Keliling'" 
                                    :class="sensorMode === 'Keliling' ? 'bg-[#2563eb] text-white border-transparent shadow-lg shadow-blue-500/20' : 'bg-[#0c1322]/60 text-slate-400 border border-[#233554] hover:text-white hover:bg-slate-800/60'"
                                    class="py-3 rounded-xl text-sm font-semibold flex items-center justify-center space-x-2 transition duration-200">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    <span>Keliling</span>
                                </button>
                            </div>
                        </div>

                        <!-- Lokasi wadah -->
                        <div>
                            <label for="location_name" class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Lokasi atau catatan wadah</label>
                            <input 
                                type="text" 
                                id="location_name"
                                name="location_name" 
                                required 
                                placeholder="Ember biru, sudut kiri gudang" 
                                class="w-full bg-[#0c1322]/60 border border-[#233554] text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition duration-200 placeholder-slate-600">
                        </div>

                        <!-- Validation Errors -->
                        @if ($errors->any())
                        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-4">
                            <ul class="text-rose-400 text-xs space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="flex items-center space-x-2">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                        </svg>
                                        <span>{{ $error }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                class="w-full sm:w-auto bg-[#2563eb] hover:bg-blue-600 active:scale-[0.98] text-white font-bold px-8 py-3.5 rounded-xl transition duration-200 flex items-center justify-center space-x-2.5 shadow-lg shadow-blue-500/20">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Daftarkan rendaman</span>
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Info Section: Quick Tips -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-6 hover:border-emerald-500/20 transition duration-300">
                    <div class="bg-emerald-500/10 w-10 h-10 rounded-xl flex items-center justify-center mb-4 border border-emerald-500/20">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-2">Pilih Metode yang Tepat</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Air garam paling efektif untuk menghilangkan racun dioscorine dari gadung. Gunakan konsentrasi 3-5% untuk hasil optimal.</p>
                </div>

                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-6 hover:border-amber-500/20 transition duration-300">
                    <div class="bg-amber-500/10 w-10 h-10 rounded-xl flex items-center justify-center mb-4 border border-amber-500/20">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-2">Waktu Perendaman</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Proses perendaman optimal berlangsung 48–72 jam dengan penggantian air setiap 12 jam. Monitor status secara berkala.</p>
                </div>

                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-6 hover:border-blue-500/20 transition duration-300">
                    <div class="bg-blue-500/10 w-10 h-10 rounded-xl flex items-center justify-center mb-4 border border-blue-500/20">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A18.168 18.168 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-2">Mode Sensor</h4>
                    <p class="text-xs text-slate-400 leading-relaxed"><strong class="text-white">Menetap</strong> — sensor dipasang permanen. <strong class="text-white">Keliling</strong> — sensor dirotasi antar beberapa wadah.</p>
                </div>
            </section>
        </main>

        <!-- Dynamic Success Toast -->
        @if(session('success'))
        <div 
            x-show="showToast"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform translate-y-12 opacity-0"
            x-transition:enter-end="transform translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="transform translate-y-0 opacity-100"
            x-transition:leave-end="transform translate-y-12 opacity-0"
            class="fixed bottom-6 right-6 bg-[#132d2f] border border-emerald-500/40 text-emerald-200 px-5 py-4 rounded-xl shadow-2xl z-50 flex items-center space-x-3.5">
            <div class="bg-emerald-500/20 p-1.5 rounded-lg border border-emerald-500/30">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">Pendaftaran Berhasil!</p>
                <p class="text-xs text-emerald-400/80 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Alpine JS Application Logic -->
    <script>
        function rendamanApp() {
            return {
                sidebarOpen: false,
                sensorMode: 'Menetap',
                showToast: {{ session('success') ? 'true' : 'false' }},

                init() {
                    if (this.showToast) {
                        setTimeout(() => {
                            this.showToast = false;
                        }, 5000);
                    }
                }
            }
        }
    </script>
</body>
</html>
