<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gadungGuard - Alur Detoksifikasi</title>
    
    <!-- Tailwind CSS (via Vite bundle, styled with Tailwind CSS v4) -->
    @vite(['resources/css/app.css'])

    <!-- Google Fonts: Outfit -->
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
        /* Pulse animations */
        @keyframes pulseRadar {
            0% { transform: scale(0.9); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 0.4; }
            100% { transform: scale(0.9); opacity: 0.8; }
        }
        .sensor-radar {
            animation: pulseRadar 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="text-slate-100 overflow-x-hidden antialiased">

    <!-- Main Container -->
    <div class="flex min-h-screen" x-data="processApp()">
        
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
                    <a href="{{ route('process') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500/10 to-transparent text-emerald-400 border-l-2 border-emerald-500 font-medium transition duration-200">
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
                    <a href="{{ route('rendaman') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition duration-200">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Daftarkan Rendaman</span>
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
                    <button @click="sidebarOpen = true" class="md:hidden mr-3 p-2 rounded-xl bg-[#151f32] border border-[#233554] text-slate-300 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Alur Detoksifikasi Gadung</h1>
                        <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Pantau status pengerjaan dan hubungkan sensor IoT ke bak perendaman aktif</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Tambah Sensor Button -->
                    <button 
                        @click="showAddSensorModal = true"
                        class="bg-emerald-500 hover:bg-emerald-600 active:scale-[0.98] text-slate-950 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center space-x-1.5 transition shadow-lg shadow-emerald-500/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Sensor</span>
                    </button>

                    <!-- Live refresh indicator -->
                    <div class="flex items-center space-x-1.5 bg-[#151f32] border border-[#233554] px-3.5 py-2 rounded-xl text-xs text-emerald-400 font-medium">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Auto Sync Aktif</span>
                    </div>

                    <!-- User Profile Card -->
                    <div class="flex items-center space-x-2 bg-[#151f32] px-3.5 py-1.5 rounded-xl border border-[#233554]">
                        <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center font-bold text-xs text-slate-900">
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <span class="hidden sm:inline text-xs sm:text-sm font-medium text-slate-200">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    </div>
                </div>
            </header>

            <!-- Metrics Summary Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat 1: Total Active Batches -->
                <div class="bg-[#151f32]/65 border border-[#233554] p-5 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Batch Aktif</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1.5" x-text="getActiveBatchCount()">0</h3>
                    </div>
                    <div class="bg-blue-500/10 p-3 rounded-xl border border-blue-500/20 text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                </div>

                <!-- Stat 2: Active Soaking -->
                <div class="bg-[#151f32]/65 border border-[#233554] p-5 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Sedang Direndam</p>
                        <h3 class="text-3xl font-extrabold text-amber-400 mt-1.5" x-text="getStageCount('soaking')">0</h3>
                    </div>
                    <div class="bg-amber-500/10 p-3 rounded-xl border border-amber-500/20 text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Stat 3: Active Sun-Drying -->
                <div class="bg-[#151f32]/65 border border-[#233554] p-5 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Sedang Dijemur</p>
                        <h3 class="text-3xl font-extrabold text-cyan-400 mt-1.5" x-text="getStageCount('drying')">0</h3>
                    </div>
                    <div class="bg-cyan-500/10 p-3 rounded-xl border border-cyan-500/20 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Stat 4: Hardware Sensors -->
                <div class="bg-[#151f32]/65 border border-[#233554] p-5 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Konektivitas Sensor</p>
                        <h3 class="text-3xl font-extrabold text-emerald-400 mt-1.5" x-text="sensors.length">0</h3>
                    </div>
                    <div class="bg-emerald-500/10 p-3 rounded-xl border border-emerald-500/20 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                        </svg>
                    </div>
                </div>
            </section>

            <!-- Kanban Board Grid -->
            <section class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <!-- COLUMN 1: PERENDAMAN (SOAKING) -->
                <div class="bg-[#0f172a]/70 rounded-2xl border border-slate-800 p-4 flex flex-col min-h-[500px]">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-bold text-slate-200">1. Perendaman</span>
                        </div>
                        <span class="bg-amber-500/10 text-amber-400 text-xs px-2 py-0.5 rounded-md font-semibold" x-text="getStageCount('soaking')"></span>
                    </div>

                    <div class="space-y-4 flex-1 overflow-y-auto max-h-[550px] pr-1">
                        <template x-for="device in getBatchesInStage('soaking')" :key="device.id">
                            <div class="bg-[#151f32]/80 border border-[#233554] rounded-xl p-4 hover:border-slate-600 transition duration-200 flex flex-col space-y-3 relative group">
                                
                                <!-- Card Header -->
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide" x-text="device.device_code"></span>
                                        <h4 class="font-bold text-white text-sm mt-0.5" x-text="device.location_name"></h4>
                                    </div>
                                    
                                    <!-- Dynamic IoT Sensor Attachment Badge -->
                                    <template x-if="device.hardware_sensor">
                                        <div class="flex items-center space-x-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded-lg">
                                            <span class="sensor-radar inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span x-text="device.hardware_sensor.name"></span>
                                        </div>
                                    </template>
                                    <template x-if="!device.hardware_sensor">
                                        <span class="bg-slate-800 border border-slate-700 text-slate-500 text-[10px] font-semibold px-2 py-0.5 rounded-lg">Tanpa Sensor</span>
                                    </template>
                                </div>

                                <!-- Batch Details -->
                                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400 bg-[#0c1322]/55 p-2 rounded-lg">
                                    <div>Metode: <strong class="text-slate-300" x-text="device.detoxification_method"></strong></div>
                                    <div>Berat: <strong class="text-slate-300" x-text="device.yam_weight + ' kg'"></strong></div>
                                    <div>Air: <strong class="text-slate-300" x-text="device.water_volume + ' L'"></strong></div>
                                    <div>Tebal: <strong class="text-slate-300" x-text="device.slice_thickness + ' mm'"></strong></div>
                                </div>

                                <!-- Sensor Data / Projection Box -->
                                <div class="border-t border-slate-800/80 pt-3">
                                    <!-- CASE A: Has sensor + online -->
                                    <template x-if="device.hardware_sensor && isSensorOnline(device)">
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="font-medium text-slate-400">Live Telemetri:</span>
                                                <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded"
                                                      :class="getSafetyStatusClass(getLatestLog(device).safety_status)"
                                                      x-text="getLatestLog(device).safety_status"></span>
                                            </div>
                                            <div class="grid grid-cols-3 gap-1.5 text-center text-xs">
                                                <div class="bg-[#0f172a] rounded p-1">
                                                    <div class="text-[9px] text-slate-500">pH</div>
                                                    <div class="font-bold text-blue-400" x-text="parseFloat(getLatestLog(device).ph_value).toFixed(1)"></div>
                                                </div>
                                                <div class="bg-[#0f172a] rounded p-1">
                                                    <div class="text-[9px] text-slate-500">TDS</div>
                                                    <div class="font-bold text-amber-400" x-text="parseInt(getLatestLog(device).tds_value)"></div>
                                                </div>
                                                <div class="bg-[#0f172a] rounded p-1">
                                                    <div class="text-[9px] text-slate-500">HCN Est</div>
                                                    <div class="font-bold text-violet-400 text-[10px]" x-text="parseFloat(getLatestLog(device).hcn_estimated).toFixed(3)"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- CASE B: Has sensor + offline -->
                                    <template x-if="device.hardware_sensor && !isSensorOnline(device)">
                                        <div class="flex items-center space-x-2 bg-rose-500/5 border border-rose-500/10 text-rose-300 p-2.5 rounded-lg text-xs leading-snug">
                                            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <span>Kabel / Modul Offline. Periksa catu daya sensor.</span>
                                        </div>
                                    </template>

                                    <!-- CASE C: No sensor (Proyeksi Waktu) -->
                                    <template x-if="!device.hardware_sensor">
                                        <div class="space-y-2 text-xs">
                                            <div class="flex justify-between items-center text-[10px] text-slate-500 font-semibold uppercase tracking-wider">
                                                <span>Proyeksi Detoksifikasi:</span>
                                                <span class="text-emerald-400" x-text="getOfflineProgress(device) + '%'"></span>
                                            </div>
                                            <div class="w-full bg-[#0c1322] rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-emerald-600 to-emerald-400 h-full rounded-full"
                                                     :style="'width: ' + getOfflineProgress(device) + '%'"></div>
                                            </div>
                                            <p class="text-[9px] text-slate-500 leading-normal italic text-center">Berdasarkan waktu & metode perendaman</p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Sensor Assign Menu -->
                                <div class="border-t border-slate-800/80 pt-3 relative" x-data="{ dropdownOpen: false }">
                                    <div class="flex items-center justify-between gap-2">
                                        <button 
                                            @click="dropdownOpen = !dropdownOpen"
                                            class="flex-1 bg-[#0c1322]/80 border border-[#233554] hover:bg-slate-800 hover:text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center justify-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                                            </svg>
                                            <span x-text="device.hardware_sensor ? 'Pindahkan Sensor' : 'Pasang Sensor'"></span>
                                        </button>
                                        
                                        <!-- Release current sensor button -->
                                        <template x-if="device.hardware_sensor">
                                            <button 
                                                @click="assignSensor(device.hardware_sensor.id, null)"
                                                class="bg-rose-500/10 border border-rose-500/30 hover:bg-rose-600 hover:text-slate-900 p-1.5 rounded-lg text-rose-400 transition"
                                                title="Lepas Sensor">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Floating dropdown menu of sensors -->
                                    <div 
                                        x-show="dropdownOpen"
                                        @click.away="dropdownOpen = false"
                                        x-transition
                                        class="absolute left-0 right-0 mt-1 rounded-xl bg-[#131c2e] border border-[#233554] shadow-2xl z-20 overflow-hidden max-h-48 overflow-y-auto">
                                        <div class="py-1">
                                            <template x-for="sensor in sensors" :key="sensor.id">
                                                <div class="flex items-center justify-between px-3.5 py-2 text-xs transition"
                                                     :class="sensor.assigned_device_id === device.id ? 'bg-emerald-500/10' : 'hover:bg-slate-800'">
                                                    <button 
                                                        @click="assignSensor(sensor.id, device.id); dropdownOpen = false"
                                                        :disabled="sensor.assigned_device_id === device.id"
                                                        class="flex-1 text-left flex items-center space-x-2 disabled:cursor-not-allowed"
                                                        :class="sensor.assigned_device_id === device.id ? 'text-emerald-400 font-medium' : 'text-slate-300'">
                                                        <div class="flex-1">
                                                            <span class="block font-medium" x-text="sensor.name"></span>
                                                            <span class="text-[9px] text-slate-500" x-text="sensor.chip_identifier"></span>
                                                        </div>
                                                        <div class="flex items-center space-x-1.5">
                                                            <!-- Online/Offline badge -->
                                                            <span class="text-[9px] px-1.5 py-0.5 rounded border"
                                                                  :class="isSensorHwOnline(sensor) ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 'border-slate-700 bg-slate-800 text-slate-500'"
                                                                  x-text="isSensorHwOnline(sensor) ? 'Online' : 'Offline'"></span>
                                                            <span class="text-[9px] px-1.5 py-0.5 rounded border border-slate-700 bg-slate-800 text-slate-400"
                                                                  x-text="sensor.assigned_device_id ? 'Sibuk' : 'Tersedia'"></span>
                                                        </div>
                                                    </button>
                                                    <!-- Delete sensor button -->
                                                    <button 
                                                        @click.stop="deleteSensor(sensor.id)"
                                                        class="ml-2 p-1 rounded hover:bg-rose-500/20 text-slate-600 hover:text-rose-400 transition shrink-0"
                                                        title="Hapus sensor">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="sensors.length === 0">
                                                <div class="px-3.5 py-3 text-center text-xs text-slate-500">Belum ada sensor terdaftar. Nyalakan ESP32 atau klik "Tambah Sensor".</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Move Stage Control -->
                                <button 
                                    @click="moveStage(device.id, 'rinsing')"
                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2.5 rounded-xl text-xs transition duration-150 flex items-center justify-center space-x-1.5 shadow-lg shadow-blue-500/10">
                                    <span>Pindahkan ke Pencucian</span>
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <template x-if="getBatchesInStage('soaking').length === 0">
                            <div class="px-4 py-8 text-center text-xs text-slate-600 border border-dashed border-slate-800/80 rounded-xl">Tidak ada bak di tahap ini.</div>
                        </template>
                    </div>
                </div>

                <!-- COLUMN 2: PENCUCIAN (RINSING) -->
                <div class="bg-[#0f172a]/70 rounded-2xl border border-slate-800 p-4 flex flex-col min-h-[500px]">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="font-bold text-slate-200">2. Pencucian</span>
                        </div>
                        <span class="bg-blue-500/10 text-blue-400 text-xs px-2 py-0.5 rounded-md font-semibold" x-text="getStageCount('rinsing')"></span>
                    </div>

                    <div class="space-y-4 flex-1 overflow-y-auto max-h-[550px] pr-1">
                        <template x-for="device in getBatchesInStage('rinsing')" :key="device.id">
                            <div class="bg-[#151f32]/80 border border-[#233554] rounded-xl p-4 hover:border-slate-600 transition duration-200 flex flex-col space-y-3">
                                <div>
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide" x-text="device.device_code"></span>
                                    <h4 class="font-bold text-white text-sm mt-0.5" x-text="device.location_name"></h4>
                                    <p class="text-[10px] text-slate-500 mt-1 italic">Proses pencucian membuang residu dioscorine permukaan.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400 bg-[#0c1322]/55 p-2 rounded-lg">
                                    <div>Metode: <strong class="text-slate-300" x-text="device.detoxification_method"></strong></div>
                                    <div>Berat: <strong class="text-slate-300" x-text="device.yam_weight + ' kg'"></strong></div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2">
                                    <button 
                                        @click="moveStage(device.id, 'soaking')"
                                        class="flex-1 bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 py-2 rounded-lg text-xs font-semibold">
                                        Kembali Soak
                                    </button>
                                    <button 
                                        @click="moveStage(device.id, 'drying')"
                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg text-xs font-bold shadow-lg shadow-blue-500/15">
                                        Jemur Gadung
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="getBatchesInStage('rinsing').length === 0">
                            <div class="px-4 py-8 text-center text-xs text-slate-600 border border-dashed border-slate-800/80 rounded-xl">Tidak ada bak di tahap ini.</div>
                        </template>
                    </div>
                </div>

                <!-- COLUMN 3: PENGERINGAN (DRYING) -->
                <div class="bg-[#0f172a]/70 rounded-2xl border border-slate-800 p-4 flex flex-col min-h-[500px]">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                            <span class="font-bold text-slate-200">3. Pengeringan</span>
                        </div>
                        <span class="bg-cyan-500/10 text-cyan-400 text-xs px-2 py-0.5 rounded-md font-semibold" x-text="getStageCount('drying')"></span>
                    </div>

                    <div class="space-y-4 flex-1 overflow-y-auto max-h-[550px] pr-1">
                        <template x-for="device in getBatchesInStage('drying')" :key="device.id">
                            <div class="bg-[#151f32]/80 border border-[#233554] rounded-xl p-4 hover:border-slate-600 transition duration-200 flex flex-col space-y-3">
                                <div>
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide" x-text="device.device_code"></span>
                                    <h4 class="font-bold text-white text-sm mt-0.5" x-text="device.location_name"></h4>
                                    <p class="text-[10px] text-slate-500 mt-1 italic">Pengeringan matahari merusak racun sianogenik yang tersisa.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400 bg-[#0c1322]/55 p-2 rounded-lg">
                                    <div>Metode: <strong class="text-slate-300" x-text="device.detoxification_method"></strong></div>
                                    <div>Berat: <strong class="text-slate-300" x-text="device.yam_weight + ' kg'"></strong></div>
                                </div>

                                <div class="flex gap-2">
                                    <button 
                                        @click="moveStage(device.id, 'rinsing')"
                                        class="flex-1 bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 py-2 rounded-lg text-xs font-semibold">
                                        Kembali Cuci
                                    </button>
                                    <button 
                                        @click="moveStage(device.id, 'completed')"
                                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-slate-950 py-2 rounded-lg text-xs font-bold shadow-lg shadow-emerald-500/10">
                                        Verifikasi Aman
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="getBatchesInStage('drying').length === 0">
                            <div class="px-4 py-8 text-center text-xs text-slate-600 border border-dashed border-slate-800/80 rounded-xl">Tidak ada bak di tahap ini.</div>
                        </template>
                    </div>
                </div>

                <!-- COLUMN 4: SELESAI (COMPLETED) -->
                <div class="bg-[#0f172a]/70 rounded-2xl border border-slate-800 p-4 flex flex-col min-h-[500px]">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-slate-200">4. Siap Konsumsi</span>
                        </div>
                        <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2 py-0.5 rounded-md font-semibold" x-text="getStageCount('completed')"></span>
                    </div>

                    <div class="space-y-4 flex-1 overflow-y-auto max-h-[550px] pr-1">
                        <template x-for="device in getBatchesInStage('completed')" :key="device.id">
                            <div class="bg-emerald-950/15 border border-emerald-500/20 rounded-xl p-4 hover:border-emerald-500/40 transition duration-200 flex flex-col space-y-3 relative overflow-hidden">
                                <!-- Checkmark water mark -->
                                <div class="absolute -bottom-6 -right-6 opacity-5 text-emerald-500">
                                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>

                                <div class="relative z-10">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-bold text-emerald-500/70 uppercase tracking-wide" x-text="device.device_code"></span>
                                        <span class="bg-emerald-500/10 text-emerald-400 text-[9px] font-bold px-2 py-0.5 rounded border border-emerald-500/20">AMANKAN</span>
                                    </div>
                                    <h4 class="font-bold text-white text-sm mt-1" x-text="device.location_name"></h4>
                                    <p class="text-[10px] text-emerald-300/80 mt-1 leading-normal">Bahan pangan gadung teruji bebas Dioscorine & HCN secara sensoris & telemetri.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-[10px] text-slate-400 bg-emerald-950/20 border border-emerald-500/5 p-2 rounded-lg relative z-10">
                                    <div>Metode: <strong class="text-slate-300" x-text="device.detoxification_method"></strong></div>
                                    <div>Berat: <strong class="text-slate-300" x-text="device.yam_weight + ' kg'"></strong></div>
                                </div>

                                <button 
                                    @click="moveStage(device.id, 'drying')"
                                    class="relative z-10 w-full bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 py-1.5 rounded-lg text-xs font-semibold transition">
                                    Re-evaluasi (Kembali Jemur)
                                </button>
                            </div>
                        </template>

                        <template x-if="getBatchesInStage('completed').length === 0">
                            <div class="px-4 py-8 text-center text-xs text-slate-600 border border-dashed border-slate-800/80 rounded-xl">Belum ada batch yang selesai.</div>
                        </template>
                    </div>
                </div>

            </section>
        </main>

        <!-- Dynamic Success Toast -->
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
                <p class="text-sm font-bold text-white">Sinkronisasi Sukses</p>
                <p class="text-xs text-emerald-400/80 mt-0.5" x-text="toastMessage"></p>
            </div>
        </div>

        <!-- Add Sensor Modal -->
        <div x-show="showAddSensorModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
             @click.self="showAddSensorModal = false">
            <div class="bg-[#131c2e] border border-[#233554] rounded-2xl p-6 w-full max-w-md shadow-2xl"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform scale-95 opacity-0"
                 x-transition:enter-end="transform scale-100 opacity-100">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="font-bold text-white text-lg">Daftarkan Sensor Baru</h3>
                    <button @click="showAddSensorModal = false" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nama Sensor</label>
                        <input type="text" x-model="newSensorName" 
                               placeholder="Contoh: Sensor Bak-A"
                               class="w-full bg-[#0c1322] border border-[#233554] rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Chip ID / MAC Address</label>
                        <input type="text" x-model="newSensorChipId" 
                               placeholder="Contoh: AA:BB:CC:DD:EE:FF"
                               class="w-full bg-[#0c1322] border border-[#233554] rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition font-mono">
                        <p class="text-[10px] text-slate-500 mt-1">MAC Address bisa dilihat di Serial Monitor Arduino IDE saat ESP32 terkoneksi WiFi.</p>
                    </div>
                    <div x-show="addSensorError" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs px-3 py-2 rounded-lg" x-text="addSensorError"></div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="showAddSensorModal = false" 
                            class="flex-1 bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 py-2.5 rounded-xl text-sm font-semibold transition">
                        Batal
                    </button>
                    <button @click="addSensor()" 
                            :disabled="!newSensorName || !newSensorChipId"
                            :class="(!newSensorName || !newSensorChipId) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-600 active:scale-[0.98]'"
                            class="flex-1 bg-emerald-500 text-slate-950 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-emerald-500/10">
                        Daftarkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function processApp() {
            return {
                devices: @json($devices),
                sensors: @json($hardwareSensors),
                serverTime: "{{ $serverTime }}",
                
                sidebarOpen: false,
                showToast: false,
                toastMessage: '',
                pollingInterval: null,

                // Add Sensor Modal state
                showAddSensorModal: false,
                newSensorName: '',
                newSensorChipId: '',
                addSensorError: '',

                init() {
                    this.startPolling();
                },

                // ── Kanban Filter Helpers ──
                getActiveBatchCount() {
                    return this.devices.filter(d => d.process_stage !== 'completed').length;
                },

                getStageCount(stage) {
                    return this.devices.filter(d => d.process_stage === stage).length;
                },

                getBatchesInStage(stage) {
                    return this.devices.filter(d => d.process_stage === stage);
                },

                getLatestLog(device) {
                    if (!device.sensor_logs || device.sensor_logs.length === 0) return null;
                    let latest = device.sensor_logs[0];
                    for (let i = 1; i < device.sensor_logs.length; i++) {
                        if (new Date(device.sensor_logs[i].created_at) > new Date(latest.created_at)) {
                            latest = device.sensor_logs[i];
                        }
                    }
                    return latest;
                },

                // ── Sensor Status Logic ──
                isSensorOnline(device) {
                    const log = this.getLatestLog(device);
                    if (!log) return false;
                    const logTime = new Date(log.created_at).getTime();
                    const sTime = new Date(this.serverTime).getTime();
                    // Older than 15 seconds is offline
                    return (sTime - logTime) < 15000;
                },

                getOfflineProgress(device) {
                    // Estimate progression mathematically
                    // Soaking normally takes 48 hours to complete. We estimate based on created_at.
                    const start = new Date(device.created_at).getTime();
                    const now = new Date(this.serverTime).getTime();
                    const diffHours = (now - start) / (1000 * 60 * 60);
                    // Salt water (Air Garam) takes ~48h, running water (Air mengalir) ~24h, ash (Abu dapur) ~72h
                    let targetHours = 48;
                    if (device.detoxification_method === 'Air mengalir') targetHours = 24;
                    if (device.detoxification_method === 'Abu dapur') targetHours = 72;

                    const progress = Math.min(Math.round((diffHours / targetHours) * 100), 100);
                    return progress;
                },

                getSafetyStatusClass(status) {
                    if (status === 'Aman') return 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400';
                    if (status === 'Proses') return 'bg-amber-500/10 border border-amber-500/30 text-amber-400';
                    return 'bg-rose-500/10 border border-rose-500/30 text-rose-400';
                },

                // Cek apakah hardware sensor online berdasarkan last_seen_at
                isSensorHwOnline(sensor) {
                    if (!sensor.last_seen_at) return false;
                    const lastSeen = new Date(sensor.last_seen_at).getTime();
                    const now = new Date(this.serverTime).getTime();
                    return (now - lastSeen) < 15000; // < 15 detik = online
                },

                // ── Dynamic Stage Updating ──
                moveStage(deviceId, newStage) {
                    fetch('/process/update-stage', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            device_id: deviceId,
                            stage: newStage
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const devIndex = this.devices.findIndex(d => d.id === deviceId);
                            if (devIndex !== -1) {
                                this.devices[devIndex].process_stage = newStage;
                            }
                            this.triggerToast(data.message);
                        }
                    });
                },

                // ── Sensor Assignment Routing ──
                assignSensor(sensorId, deviceId) {
                    fetch('/process/assign-sensor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            sensor_id: sensorId,
                            device_id: deviceId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Update local states
                            this.sensors.forEach(s => {
                                if (s.id === sensorId) {
                                    s.assigned_device_id = deviceId;
                                }
                            });
                            
                            this.devices.forEach(d => {
                                // Clear old hardware sensor link if it was linked to this sensor
                                if (d.hardware_sensor && d.hardware_sensor.id === sensorId) {
                                    d.hardware_sensor = null;
                                }
                                // Assign to new device link
                                if (d.id === deviceId) {
                                    const matchedSensor = this.sensors.find(s => s.id === sensorId);
                                    d.hardware_sensor = matchedSensor;
                                }
                            });
                            
                            this.triggerToast(data.message);
                        }
                    });
                },

                // ── Add Sensor (Manual Registration) ──
                addSensor() {
                    this.addSensorError = '';
                    fetch('/process/add-sensor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: this.newSensorName,
                            chip_identifier: this.newSensorChipId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.sensors.push(data.sensor);
                            this.showAddSensorModal = false;
                            this.newSensorName = '';
                            this.newSensorChipId = '';
                            this.triggerToast(data.message);
                        } else if (data.errors) {
                            const firstKey = Object.keys(data.errors)[0];
                            this.addSensorError = data.errors[firstKey][0];
                        }
                    })
                    .catch(err => {
                        this.addSensorError = 'Terjadi kesalahan. Coba lagi.';
                    });
                },

                // ── Delete Sensor ──
                deleteSensor(sensorId) {
                    if (!confirm('Hapus sensor ini? Data log sensor tetap tersimpan.')) return;
                    
                    fetch('/process/delete-sensor/' + sensorId, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.sensors = this.sensors.filter(s => s.id !== sensorId);
                            // Clear hardware_sensor reference from devices
                            this.devices.forEach(d => {
                                if (d.hardware_sensor && d.hardware_sensor.id === sensorId) {
                                    d.hardware_sensor = null;
                                }
                            });
                            this.triggerToast(data.message);
                        }
                    });
                },

                triggerToast(message) {
                    this.toastMessage = message;
                    this.showToast = true;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 4000);
                },

                // ── Live Polling for Kanban values ──
                startPolling() {
                    this.pollingInterval = setInterval(() => {
                        // Fetch batch data periodically to update logs & serverTime
                        // Using route /process (or fetch endpoint)
                        fetch('/process')
                            .then(res => res.text())
                            .then(html => {
                                // Extract the JSON payload dynamically from HTML script
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = html;
                                const scriptContent = tempDiv.querySelector('script').innerHTML;
                                
                                // Parse serverTime and devices
                                // Alternatively, fetch dynamic data from a JSON endpoint.
                                // We can hit /devices/X/data for the active soaking ones, or just query process updates.
                                // Since /process view has simple JSON bindings, let's fetch updates from the server
                                // using a lightweight AJAX fetch to avoid heavy rendering
                                this.fetchProcessUpdates();
                            });
                    }, 5000);
                },

                fetchProcessUpdates() {
                    // Hitting dynamic data fetch
                    fetch('/devices/' + (this.devices[0]?.id || 1) + '/data') // Query first or loop soaking ones
                        .then(res => res.json())
                        .then(data => {
                            this.serverTime = data.server_time || new Date().toISOString();
                            // Fetch all device logs in parallel for soaking devices
                            const soakingDevices = this.devices.filter(d => d.process_stage === 'soaking' && d.hardware_sensor);
                            
                            soakingDevices.forEach(d => {
                                fetch(`/devices/${d.id}/data`)
                                    .then(r => r.json())
                                    .then(deviceData => {
                                        const devIndex = this.devices.findIndex(dev => dev.id === d.id);
                                        if (devIndex !== -1) {
                                            this.devices[devIndex].sensor_logs = deviceData.logs;
                                        }
                                    });
                            });
                        });
                }
            }
        }
    </script>
</body>
</html>
