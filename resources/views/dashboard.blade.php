<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gadungGuard - Water Quality Dashboard</title>
    
    <!-- Tailwind CSS (via Vite bundle, styled with Tailwind CSS v4) -->
    @vite(['resources/css/app.css'])

    <!-- Google Fonts: Instrument Sans (configured in Vite) & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js and Alpine.js via CDNs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <div class="flex min-h-screen" x-data="dashboardApp()">
        
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
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500/10 to-transparent text-emerald-400 border-l-2 border-emerald-500 font-medium transition duration-200">
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
                    <a href="{{ route('rendaman') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200 text-left">
    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Water Quality Dashboard</h1>
                        <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Real-time wild yam (gadung) soaking monitor</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end mt-2 sm:mt-0">
                    <!-- Live Polling Toggle -->
                    <button 
                        @click="togglePolling()" 
                        :class="isPolling ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-slate-800/40 border-slate-700/50 text-slate-400'"
                        class="flex items-center space-x-2 px-2.5 sm:px-3 py-2 rounded-xl border text-xs sm:text-sm font-medium transition duration-200 hover:bg-slate-800/80">
                        <span class="relative flex h-2 w-2 sm:h-2.5 sm:w-2.5">
                            <span :class="isPolling ? 'animate-ping bg-emerald-400' : 'bg-slate-500'" class="absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                            <span :class="isPolling ? 'bg-emerald-500' : 'bg-slate-500'" class="relative inline-flex rounded-full h-2 w-2 sm:h-2.5 sm:w-2.5"></span>
                        </span>
                        <span class="hidden sm:inline" x-text="isPolling ? 'Live Polling Active' : 'Live Polling Paused'"></span>
                        <span class="sm:hidden" x-text="isPolling ? 'Live' : 'Paused'"></span>
                    </button>

                    <a 
    href="{{ route('rendaman') }}" 
    class="bg-emerald-500 hover:bg-emerald-600 active:scale-[0.98] text-slate-950 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center space-x-1.5 sm:space-x-2 transition shadow-lg shadow-emerald-500/10">
    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    <span>Daftarkan Rendaman</span>
</a>

                    <!-- Device Selector Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            class="bg-[#151f32]/85 border border-[#233554] px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold flex items-center space-x-1.5 sm:space-x-2 text-white hover:bg-slate-800 transition">
                            <span x-text="currentDevice.device_code"></span>
                            <span class="hidden md:inline text-xs text-emerald-400" x-text="'(' + currentDevice.location_name + ')'"></span>
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        
                        <div 
                            x-show="open" 
                            @click.away="open = false" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute right-0 mt-2 w-56 rounded-xl bg-[#131c2e] border border-[#233554] shadow-2xl z-20 overflow-hidden">
                            <div class="py-1">
                                <template x-for="dev in devices" :key="dev.id">
                                    <button 
                                        @click="selectDevice(dev); open = false" 
                                        :class="dev.id === currentDevice.id ? 'bg-emerald-500/10 text-emerald-400 font-medium' : 'text-slate-300 hover:bg-slate-800'"
                                        class="w-full text-left px-4 py-2.5 text-sm flex justify-between items-center transition">
                                        <span x-text="dev.device_code"></span>
                                        <span class="text-xs text-slate-500" x-text="dev.location_name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="relative">
                            <button class="bg-[#151f32] p-2 rounded-xl border border-[#233554] text-slate-300 hover:text-white transition relative">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a9.04 9.04 0 01-1.655-.13h-.007a3 3 0 01-2.247-2.925V9c0-3.313-2.687-6-6-6S1.5 5.687 1.5 9v5.027a3 3 0 01-2.247 2.925H-.75c-.621 0-1.125.504-1.125 1.125v1.5a3.375 3.375 0 003.375 3.375h16.5a3.375 3.375 0 003.375-3.375v-1.5c0-.621-.504-1.125-1.125-1.125h-.007zM12 21a3 3 0 01-6 0" />
                                </svg>
                                <span class="absolute top-1 right-1 w-1.5 h-1.5 sm:w-2 sm:h-2 bg-rose-500 rounded-full"></span>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2 bg-[#151f32] px-2.5 sm:px-3.5 py-1.5 rounded-xl border border-[#233554]">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-500 flex items-center justify-center font-bold text-xs text-slate-900">
                                {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                            </div>
                            <span class="hidden sm:inline text-xs sm:text-sm font-medium text-slate-200">{{ auth()->user()->name ?? 'Administrator' }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Charts & Water Quality Graph -->
            <section class="bg-[#151f32]/60 backdrop-blur-md border border-[#233554] rounded-2xl p-6 relative overflow-hidden">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <h2 class="text-lg font-bold text-white tracking-wide">Sensor Metrics Stream</h2>
                        
                        <!-- Legends with values -->
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                            <span class="flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span>pH (<span x-text="latestVal.ph"></span>)</span>
                            </span>
                            <span class="flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span>Turbidity (<span x-text="latestVal.turbidity"></span> NTU)</span>
                            </span>
                            <span class="flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <span>TDS (<span x-text="latestVal.tds"></span> ppm)</span>
                            </span>
                            <span class="flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>Temp (<span x-text="latestVal.temp"></span>°C)</span>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
                        <!-- Date Selector -->
                        <div class="flex items-center space-x-2">
                            <label class="text-xs font-semibold text-slate-400">Rekap Tanggal:</label>
                            <input 
                                type="date" 
                                x-model="filterDate"
                                @change="fetchDeviceData()"
                                class="bg-[#151f32]/85 border border-[#233554] px-3 py-1.5 rounded-lg text-xs font-semibold text-white focus:outline-none focus:border-emerald-500 transition cursor-pointer"
                                max="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Reset to Live Stream -->
                        <template x-if="filterDate">
                            <button 
                                @click="clearFilterDate()"
                                class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-emerald-500/20 transition flex items-center space-x-1.5">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                </span>
                                <span>Kembali ke Live Stream</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Main Chart Canvas & Empty State -->
                <div class="h-80 w-full relative">
                    <canvas id="mainChart" x-show="logs.length > 0"></canvas>
                    
                    <!-- Empty State Overlay -->
                    <div 
                        x-show="logs.length === 0"
                        x-cloak
                        class="absolute inset-0 flex flex-col items-center justify-center bg-[#151f32]/40 rounded-xl border border-slate-800/40 p-6 text-center">
                        <div class="bg-[#1b2a47] p-3 rounded-full mb-3 text-slate-400 border border-[#233554]">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-white">Tidak Ada Data Sensor</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">
                            Tidak ditemukan log sensor untuk rendaman ini pada tanggal <strong class="text-emerald-400" x-text="filterDate"></strong>. Silakan pilih tanggal lain atau kembali ke siaran langsung.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Cards Grid: Metrics Indicators (5 columns: pH, Turbidity, TDS, Temp, HCN) -->
            <section class="grid grid-cols-2 md:grid-cols-5 gap-4 sm:gap-6">
                <!-- Card 1: pH Level -->
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-5 flex flex-col justify-between hover:border-emerald-500/30 transition duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-semibold tracking-wider uppercase">pH Level</p>
                            <h3 class="text-3xl font-extrabold text-white mt-2" x-text="latestVal.ph">--</h3>
                        </div>
                        <div :class="getStatusBadgeClass('ph')" class="text-xs px-2 py-1 rounded-lg border font-medium uppercase tracking-wider" x-text="getValStatus('ph')"></div>
                    </div>
                    <div class="h-10 mt-4 relative">
                        <canvas id="sparklinePh"></canvas>
                    </div>
                </div>

                <!-- Card 2: Turbidity -->
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-5 flex flex-col justify-between hover:border-rose-500/30 transition duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-semibold tracking-wider uppercase">Turbidity</p>
                            <h3 class="text-3xl font-extrabold text-white mt-2"><span x-text="latestVal.turbidity">--</span> <span class="text-sm font-semibold text-slate-500">NTU</span></h3>
                        </div>
                        <div :class="getStatusBadgeClass('turbidity')" class="text-xs px-2 py-1 rounded-lg border font-medium uppercase tracking-wider" x-text="getValStatus('turbidity')"></div>
                    </div>
                    <div class="h-10 mt-4 relative">
                        <canvas id="sparklineTurbidity"></canvas>
                    </div>
                </div>

                <!-- Card 3: TDS -->
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-5 flex flex-col justify-between hover:border-blue-500/30 transition duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-semibold tracking-wider uppercase">TDS</p>
                            <h3 class="text-3xl font-extrabold text-white mt-2"><span x-text="latestVal.tds">--</span> <span class="text-sm font-semibold text-slate-500">ppm</span></h3>
                        </div>
                        <div :class="getStatusBadgeClass('tds')" class="text-xs px-2 py-1 rounded-lg border font-medium uppercase tracking-wider" x-text="getValStatus('tds')"></div>
                    </div>
                    <div class="h-10 mt-4 relative">
                        <canvas id="sparklineTds"></canvas>
                    </div>
                </div>

                <!-- Card 4: Temperature -->
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-5 flex flex-col justify-between hover:border-amber-500/30 transition duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-semibold tracking-wider uppercase">Temperature</p>
                            <h3 class="text-3xl font-extrabold text-white mt-2"><span x-text="latestVal.temp">--</span> <span class="text-sm font-semibold text-slate-500">°C</span></h3>
                        </div>
                        <div class="text-xs px-2 py-1 rounded-lg border border-slate-700/50 bg-slate-800/30 text-slate-400 font-medium uppercase tracking-wider">Stable</div>
                    </div>
                    <div class="h-10 mt-4 relative">
                        <canvas id="sparklineTemp"></canvas>
                    </div>
                </div>

                <!-- Card 5: HCN Estimation (AI Model) -->
                <div class="col-span-2 md:col-span-1 bg-[#151f32]/60 border border-violet-500/20 rounded-2xl p-5 flex flex-col justify-between hover:border-violet-500/40 transition duration-300 relative overflow-hidden">
                    <!-- AI badge -->
                    <div class="absolute top-3 right-3 bg-violet-500/10 border border-violet-500/30 text-violet-400 text-[9px] font-bold px-1.5 py-0.5 rounded tracking-widest uppercase">AI Model</div>
                    <div class="flex justify-between items-start">
                        <div class="pr-14">
                            <p class="text-slate-400 text-xs font-semibold tracking-wider uppercase">Estimasi HCN</p>
                            <h3 class="text-2xl font-extrabold text-white mt-2">
                                <span x-text="latestVal.hcn !== '--' ? parseFloat(latestVal.hcn).toFixed(3) : '--'">--</span>
                                <span class="text-xs font-semibold text-slate-500"> mg/L</span>
                            </h3>
                        </div>
                    </div>
                    <!-- HCN safety gauge bar -->
                    <div class="mt-4">
                        <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                            <span>0</span>
                            <span class="text-emerald-400">Aman &lt;0.5</span>
                            <span class="text-amber-400">Proses &lt;3</span>
                            <span class="text-rose-400">15+</span>
                        </div>
                        <div class="w-full bg-[#1b2a47] rounded-full h-2 overflow-hidden">
                            <!-- Gradient: green→yellow→red -->
                            <div class="h-full rounded-full transition-all duration-700"
                                :class="getHcnBarClass()"
                                :style="'width: ' + getHcnBarWidth() + '%'"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- AI Recommendation Banner -->
            <section
                :class="getRecommendationBannerClass()"
                class="border rounded-2xl px-6 py-4 flex items-start space-x-4 transition-all duration-500">
                <div class="shrink-0 mt-0.5">
                    <!-- Icon berubah sesuai status -->
                    <template x-if="latestStatus === 'Bahaya'">
                        <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </template>
                    <template x-if="latestStatus === 'Proses'">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </template>
                    <template x-if="latestStatus === 'Aman' || latestStatus === 'INIT' || latestStatus === ''">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-bold uppercase tracking-widest mb-0.5"
                       :class="latestStatus === 'Bahaya' ? 'text-rose-400' : latestStatus === 'Proses' ? 'text-amber-400' : 'text-emerald-400'">
                        Rekomendasi AI &mdash; <span x-text="latestStatus"></span>
                    </p>
                    <p class="text-sm text-slate-200 leading-relaxed" x-text="latestRecommendation"></p>
                </div>
                <!-- HCN inline pill -->
                <div class="shrink-0 text-right">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider">HCN Estimasi</p>
                    <p class="text-lg font-bold"
                       :class="latestStatus === 'Bahaya' ? 'text-rose-300' : latestStatus === 'Proses' ? 'text-amber-300' : 'text-emerald-300'">
                        <span x-text="latestVal.hcn !== '--' ? parseFloat(latestVal.hcn).toFixed(3) : '--'">--</span>
                        <span class="text-xs text-slate-500"> mg/L</span>
                    </p>
                </div>
            </section>



            <!-- Bottom Operations Grid -->
            <section class="grid grid-cols-1 md:grid-cols-5 gap-6">
                
                <!-- Active Process Details Card (3 Columns Wide) -->
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-6 md:col-span-3 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="font-bold text-white text-base tracking-wide flex items-center space-x-2">
                                <span>Active Process</span>
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                            </h3>
                            <span class="bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold px-2.5 py-1 rounded-lg uppercase tracking-wider">Active</span>
                        </div>

                        <!-- Progress Bar Container -->
                        <div class="mb-6">
                            <div class="flex justify-between text-xs text-slate-400 mb-2">
                                <span>Gadung detoxification cycle</span>
                                <span class="text-amber-400 font-semibold" x-text="progress + '% completed'"></span>
                            </div>
                            <div class="w-full bg-[#1b2a47] rounded-full h-3 overflow-hidden border border-[#223657]">
                                <div class="bg-gradient-to-r from-amber-500 to-amber-300 h-full rounded-full transition-all duration-1000 shadow-[0_0_12px_rgba(245,158,11,0.4)]" :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>

                        <!-- Process Status Checklist -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-5 h-5 rounded-full border border-emerald-500 bg-emerald-500/15 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-300">Initial raw yam sorting & preparation</span>
                            </div>
                            <div class="flex items-center space-x-3.5">
                                <div class="w-5 h-5 rounded-full border border-amber-500 bg-amber-500/15 flex items-center justify-center shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></div>
                                </div>
                                <span class="text-sm font-semibold text-white">Ongoing chemical extraction & soaking (TDS: <span x-text="latestVal.tds"></span> ppm)</span>
                            </div>
                            <div class="flex items-center space-x-3.5 opacity-40">
                                <div class="w-5 h-5 rounded-full border border-slate-700 flex items-center justify-center shrink-0"></div>
                                <span class="text-sm font-medium text-slate-400">Sensory control test (human verified)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Human-in-the-Loop Verification Card (2 Columns Wide) -->
                <div class="bg-[#151f32]/60 border border-[#233554] rounded-2xl p-6 md:col-span-2 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-base tracking-wide mb-3 flex items-center space-x-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.97 5.97 0 00-.75-2.985m-.001-1.015a9 9 0 019-9c0 .763-.095 1.503-.274 2.21a5.3 5.3 0 00-.868-.104H16.89M18 1.5a3 3 0 11-6 0 3 3 0 016 0zm-1.5 10.5a3 3 0 11-6 0 3 3 0 016 0zm-6 3a5.99 5.99 0 00-4.793 2.39A6.042 6.042 0 002.25 12h8.25" />
                            </svg>
                            <span>Human-in-the-Loop</span>
                        </h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            User confirmation as your control line (HITL). Once the safety status turns <strong>Aman (Safe)</strong>, verify the yam detoxification quality physically to confirm the cycle completion.
                        </p>
                    </div>

                    <!-- Interactive Action Button with dynamic state -->
                    <div>
                        <button 
                            @click="triggerConfirmation()"
                            :disabled="confirmed"
                            :class="confirmed ? 'bg-emerald-600/30 border-emerald-500/40 text-emerald-300 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold active:scale-[0.98]'"
                            class="w-full py-3.5 rounded-xl border border-transparent shadow-lg text-center transition duration-200 flex justify-center items-center space-x-2">
                            
                            <template x-if="confirmed">
                                <span class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-emerald-400 animate-bounce" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Cycle Verified & Completed</span>
                                </span>
                            </template>
                            <template x-if="!confirmed">
                                <span>User Confirmation</span>
                            </template>
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- Dynamic Success/Interaction Toast -->
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
                <p class="text-sm font-bold text-white">{{ session('success') ? 'Pendaftaran Berhasil!' : 'Soaking Cycle Confirmed!' }}</p>
                <p class="text-xs text-emerald-400/80 mt-0.5">{{ session('success') ?? 'Logs marked as physically verified. Cycle closed.' }}</p>
            </div>
        </div>


    </div>

    <!-- Dashboard Frontend Reactivity Logic -->
    <script>
        // Simpan instance Chart secara non-reaktif di luar objek data Alpine
        // untuk mencegah bug "freezing" akibat siklus reaktivitas Alpine.js
        let mainChartInstance = null;
        let sparklineInstances = {};

        function dashboardApp() {
            return {
                devices: @json($devices),
                currentDevice: @json($currentDevice),
                logs: @json($logs),
                
                isPolling: true,
                pollingInterval: null,
                showToast: {{ session('success') ? 'true' : 'false' }},
                confirmed: false,
                progress: 68,
                sidebarOpen: false,
                filterDate: '{{ date('Y-m-d') }}',
                
                latestVal: {
                    ph: '--',
                    turbidity: '--',
                    tds: '--',
                    temp: '--',
                    hcn: '--'   // Estimasi kadar HCN dari AI model ESP32
                },

                // Status dan rekomendasi terbaru
                latestStatus: 'INIT',
                latestRecommendation: 'Menunggu data dari sensor...',

                init() {
                    this.renderMainChart();
                    this.renderSparklines();
                    this.fetchDeviceData();
                    
                    if (this.showToast) {
                        setTimeout(() => {
                            this.showToast = false;
                        }, 5000);
                    }
                    
                    // Periodically increment progress to simulate real life cycle progress
                    setInterval(() => {
                        if (this.progress < 100) {
                            this.progress = Math.min(100, this.progress + 1);
                        }
                    }, 12000);
                },

                updateLatestValues() {
                    if (this.logs && this.logs.length > 0) {
                        const lastLog = this.logs[this.logs.length - 1];
                        this.latestVal.ph         = parseFloat(lastLog.ph_value).toFixed(1);
                        this.latestVal.turbidity   = parseFloat(lastLog.turbidity_value).toFixed(1);
                        this.latestVal.tds         = parseFloat(lastLog.tds_value).toFixed(1);
                        this.latestVal.temp        = parseFloat(lastLog.temperature_value).toFixed(1);
                        this.latestVal.hcn         = lastLog.hcn_estimated != null
                            ? parseFloat(lastLog.hcn_estimated).toFixed(4)
                            : '--';

                        // Sinkronisasi status & rekomendasi dari data log terbaru
                        this.latestStatus = lastLog.safety_status || 'INIT';
                        this.latestRecommendation = this.buildRecommendationText(
                            this.latestStatus,
                            parseFloat(lastLog.ph_value),
                            parseFloat(lastLog.turbidity_value),
                            parseFloat(lastLog.tds_value),
                            lastLog.hcn_estimated != null ? parseFloat(lastLog.hcn_estimated) : null
                        );
                    }
                },

                /**
                 * Bangun teks rekomendasi dari data log (mirror logika SensorController PHP).
                 */
                buildRecommendationText(status, ph, turb, tds, hcn) {
                    if (status === 'Bahaya') {
                        let detail = [];
                        if (ph < 5.5 || ph > 9.0)          detail.push(`pH ekstrem (${ph.toFixed(1)})`);
                        if (turb > 600)                    detail.push(`kekeruhan sangat tinggi (${turb.toFixed(0)} NTU)`);
                        if (tds > 700)                     detail.push(`TDS sangat tinggi (${tds.toFixed(0)} ppm)`);
                        if (hcn !== null && hcn > 3.0)     detail.push(`estimasi HCN kritis (${hcn.toFixed(3)} mg/L)`);
                        const detailStr = detail.length ? ' Penyebab: ' + detail.join(', ') + '.' : '';
                        return 'SEGERA ganti air rendaman!' + detailStr + ' Jangan konsumsi gadung sebelum status berubah menjadi Aman.';
                    }
                    if (status === 'Proses') {
                        let hints = [];
                        if (turb > 300) hints.push('ganti air lebih sering (tiap 6 jam)');
                        if (tds > 400)  hints.push('gunakan air mengalir jika memungkinkan');
                        if (ph < 6.5)   hints.push('pantau pH mendekati netral');
                        const hintsStr = hints.length ? ' Saran: ' + hints.join('; ') + '.' : '';
                        return 'Proses detoksifikasi berjalan.' + hintsStr + ' Lanjutkan perendaman dan pantau setiap 8–12 jam.';
                    }
                    if (status === 'Aman') {
                        return 'Air rendaman dalam kondisi aman. Gadung siap ditiriskan dan diolah lebih lanjut. Konfirmasi secara fisik sebelum diproses ke tahap memasak.';
                    }
                    return 'Menunggu data dari sensor...';
                },

                getValStatus(metric) {
                    if (!this.logs || this.logs.length === 0) return 'Stable';
                    const ph = parseFloat(this.latestVal.ph);
                    const turb = parseFloat(this.latestVal.turbidity);
                    const tds = parseFloat(this.latestVal.tds);

                    if (metric === 'ph') {
                        return (ph < 6.0 || ph > 8.5) ? 'Danger' : 'Safe';
                    }
                    if (metric === 'turbidity') {
                        if (turb > 400) return 'Danger';
                        if (turb > 150) return 'Warning';
                        return 'Safe';
                    }
                    if (metric === 'tds') {
                        if (tds > 500) return 'Danger';
                        if (tds > 250) return 'Warning';
                        return 'Safe';
                    }
                    return 'Safe';
                },

                getStatusBadgeClass(metric) {
                    const status = this.getValStatus(metric);
                    if (status === 'Danger') {
                        return 'bg-rose-500/10 border-rose-500/30 text-rose-400';
                    }
                    if (status === 'Warning') {
                        return 'bg-amber-500/10 border-amber-500/30 text-amber-400';
                    }
                    return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400';
                },

                /**
                 * Lebar bar gauge HCN (0–100%), skala log dari 0–15 mg/L.
                 */
                getHcnBarWidth() {
                    if (this.latestVal.hcn === '--') return 2;
                    const hcn = parseFloat(this.latestVal.hcn);
                    // Skala 0–15 mg/L → 0–100%
                    return Math.min(100, Math.max(2, (hcn / 15) * 100));
                },

                /**
                 * Warna bar gauge HCN sesuai status.
                 */
                getHcnBarClass() {
                    if (this.latestVal.hcn === '--') return 'bg-slate-600';
                    const hcn = parseFloat(this.latestVal.hcn);
                    if (hcn > 3.0)  return 'bg-gradient-to-r from-rose-500 to-rose-400';
                    if (hcn >= 0.5) return 'bg-gradient-to-r from-amber-500 to-amber-300';
                    return 'bg-gradient-to-r from-emerald-500 to-emerald-300';
                },

                /**
                 * Class background untuk banner rekomendasi AI.
                 */
                getRecommendationBannerClass() {
                    if (this.latestStatus === 'Bahaya') {
                        return 'bg-rose-500/5 border-rose-500/20';
                    }
                    if (this.latestStatus === 'Proses') {
                        return 'bg-amber-500/5 border-amber-500/20';
                    }
                    return 'bg-emerald-500/5 border-emerald-500/20';
                },

                selectDevice(device) {
                    this.currentDevice = device;
                    this.confirmed = false;
                    this.fetchDeviceData();
                },

                fetchDeviceData() {
                    let url = `/devices/${this.currentDevice.id}/data`;
                    if (this.filterDate) {
                        url += `?date=${this.filterDate}`;
                        this.isPolling = false;
                    }
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.logs = data.logs;
                            this.updateLatestValues();
                            this.updateCharts();
                        });
                },

                clearFilterDate() {
                    this.filterDate = '';
                    this.isPolling = true;
                    this.fetchDeviceData();
                    this.startPolling();
                },

                startPolling() {
                    this.pollingInterval = setInterval(() => {
                        if (this.isPolling) {
                            this.fetchDeviceData();
                        }
                    }, 5000); // Poll database every 5 seconds
                },

                togglePolling() {
                    this.isPolling = !this.isPolling;
                },

                triggerConfirmation() {
                    this.confirmed = true;
                    this.showToast = true;
                    this.progress = 100;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 4000);
                },

                updateCharts() {
                    // Re-process raw data points
                    const phData = this.logs.map(l => l.ph_value);
                    const turbData = this.logs.map(l => l.turbidity_value);
                    const tdsData = this.logs.map(l => l.tds_value);
                    const tempData = this.logs.map(l => l.temperature_value);
                    
                    const labels = this.logs.map(l => {
                        const date = new Date(l.created_at);
                        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    });

                    // Update main chart
                    if (mainChartInstance) {
                        mainChartInstance.data.labels = labels;
                        mainChartInstance.data.datasets[0].data = phData;
                        mainChartInstance.data.datasets[1].data = turbData;
                        mainChartInstance.data.datasets[2].data = tdsData;
                        mainChartInstance.data.datasets[3].data = tempData;
                        mainChartInstance.update('none');
                    }

                    // Update sparklines
                    if (sparklineInstances.ph) this.updateSparkline(sparklineInstances.ph, phData, '#10b981');
                    if (sparklineInstances.turbidity) this.updateSparkline(sparklineInstances.turbidity, turbData, '#f43f5e');
                    if (sparklineInstances.tds) this.updateSparkline(sparklineInstances.tds, tdsData, '#3b82f6');
                    if (sparklineInstances.temp) this.updateSparkline(sparklineInstances.temp, tempData, '#f59e0b');
                },

                updateSparkline(chart, data, color) {
                    chart.data.labels = data.map((_, i) => i);
                    chart.data.datasets[0].data = data;
                    chart.update('none');
                },

                renderMainChart() {
                    const ctx = document.getElementById('mainChart').getContext('2d');
                    
                    // Chart Gradients
                    const phGrad = ctx.createLinearGradient(0, 0, 0, 300);
                    phGrad.addColorStop(0, 'rgba(16, 185, 129, 0.15)');
                    phGrad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

                    const turbData = this.logs.map(l => l.turbidity_value);
                    const tdsData = this.logs.map(l => l.tds_value);
                    const tempData = this.logs.map(l => l.temperature_value);
                    const phData = this.logs.map(l => l.ph_value);

                    const labels = this.logs.map(l => {
                        const date = new Date(l.created_at);
                        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    });

                    mainChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'pH level',
                                    data: phData,
                                    borderColor: '#10b981',
                                    borderWidth: 2,
                                    fill: true,
                                    backgroundColor: phGrad,
                                    tension: 0.4,
                                    yAxisID: 'yPh',
                                    pointRadius: 0,
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: '#10b981',
                                },
                                {
                                    label: 'Turbidity',
                                    data: turbData,
                                    borderColor: '#f43f5e',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0.4,
                                    yAxisID: 'yTurb',
                                    pointRadius: 0,
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: '#f43f5e',
                                },
                                {
                                    label: 'TDS (ppm)',
                                    data: tdsData,
                                    borderColor: '#3b82f6',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0.4,
                                    yAxisID: 'yTds',
                                    pointRadius: 0,
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: '#3b82f6',
                                },
                                {
                                    label: 'Temperature (°C)',
                                    data: tempData,
                                    borderColor: '#f59e0b',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0.4,
                                    yAxisID: 'yTemp',
                                    pointRadius: 0,
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: '#f59e0b',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#131c2e',
                                    titleColor: '#fff',
                                    bodyColor: '#94a3b8',
                                    borderColor: '#233554',
                                    borderWidth: 1,
                                    padding: 12,
                                    cornerRadius: 10,
                                    callbacks: {
                                        title: (items) => 'Time: ' + items[0].label
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(35, 53, 84, 0.2)' },
                                    ticks: { color: '#64748b', maxTicksLimit: 8 }
                                },
                                yPh: {
                                    type: 'linear',
                                    position: 'left',
                                    min: 0,
                                    max: 14,
                                    grid: { color: 'rgba(35, 53, 84, 0.25)' },
                                    ticks: { color: '#10b981' }
                                },
                                yTurb: {
                                    type: 'linear',
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: { color: '#f43f5e' }
                                },
                                yTds: {
                                    type: 'linear',
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: { color: '#3b82f6' }
                                },
                                yTemp: {
                                    type: 'linear',
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: { color: '#f59e0b' }
                                }
                            }
                        }
                    });
                },

                renderSparklines() {
                    const sparklineConfigs = [
                        { id: 'sparklinePh', data: this.logs.map(l => l.ph_value), color: '#10b981' },
                        { id: 'sparklineTurbidity', data: this.logs.map(l => l.turbidity_value), color: '#f43f5e' },
                        { id: 'sparklineTds', data: this.logs.map(l => l.tds_value), color: '#3b82f6' },
                        { id: 'sparklineTemp', data: this.logs.map(l => l.temperature_value), color: '#f59e0b' }
                    ];

                    sparklineConfigs.forEach(cfg => {
                        const ctx = document.getElementById(cfg.id).getContext('2d');
                        
                        const grad = ctx.createLinearGradient(0, 0, 0, 40);
                        grad.addColorStop(0, cfg.color + '33'); // 20% opacity
                        grad.addColorStop(1, cfg.color + '00'); // 0% opacity

                        sparklineInstances[cfg.id.replace('sparkline', '').toLowerCase()] = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: cfg.data.map((_, i) => i),
                                datasets: [{
                                    data: cfg.data,
                                    borderColor: cfg.color,
                                    borderWidth: 1.5,
                                    fill: true,
                                    backgroundColor: grad,
                                    pointRadius: 0,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                                scales: {
                                    x: { display: false },
                                    y: { display: false }
                                }
                            }
                        });
                    });
                }
            }
        }
    </script>
</body>
</html>
