<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gadungGuard - Sensor Status</title>
    
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

        /* Sensor status card animations */
        @keyframes sensorPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.15); }
        }
        @keyframes sensorGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            50% { box-shadow: 0 0 20px 4px rgba(16, 185, 129, 0.15); }
        }
        @keyframes sensorGlowRed {
            0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
            50% { box-shadow: 0 0 20px 4px rgba(244, 63, 94, 0.15); }
        }
        .sensor-card-online {
            animation: sensorGlow 3s ease-in-out infinite;
        }
        .sensor-card-offline {
            animation: sensorGlowRed 2s ease-in-out infinite;
        }
        .sensor-pulse-dot {
            animation: sensorPulse 2s ease-in-out infinite;
        }
        .sensor-icon-wrapper {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sensor-card:hover .sensor-icon-wrapper {
            transform: translateY(-2px) scale(1.05);
        }
    </style>
</head>
<body class="text-slate-100 overflow-x-hidden antialiased">

    <!-- Main Container -->
    <div class="flex min-h-screen" x-data="sensoryApp()">
        
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
                    <a href="{{ route('sensory') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500/10 to-transparent text-emerald-400 border-l-2 border-emerald-500 font-medium transition duration-200">
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
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Sensory Status Monitor</h1>
                        <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Real-time wild yam (gadung) hardware sensor connection status</p>
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

                    <!-- Register New Immersion Link -->
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
            <!-- SENSOR STATUS PANEL — Menampilkan keberadaan sensor    -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <section class="bg-[#151f32]/60 backdrop-blur-md border border-[#233554] rounded-2xl p-8 relative overflow-hidden">
                <!-- Section Header -->
                <div class="flex justify-between items-center mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="bg-cyan-500/10 p-3 rounded-xl border border-cyan-500/30">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white tracking-wide">Hardware Sensor Status</h2>
                            <p class="text-sm text-slate-400 mt-1">Sistem mendeteksi otomatis keberadaan modul sensor secara real-time</p>
                        </div>
                    </div>
                    
                    <!-- Summary badge -->
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-slate-400 font-medium">Status Sensor Terhubung:</span>
                        <span :class="deviceOnline ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/15 border border-rose-500/30 text-rose-400'" 
                              class="text-sm font-bold px-3 py-1.5 rounded-xl shadow-lg shadow-emerald-500/5" 
                              x-text="deviceOnline ? getSensorOnlineCount() + ' / 4 Online' : 'Device Offline'">- / 4 Online</span>
                    </div>
                </div>

                <!-- Sensor Cards Grid (4 columns) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    <!-- Sensor 1: pH Analog -->
                    <div class="sensor-card relative rounded-2xl p-6 border transition-all duration-500 cursor-default group"
                         :class="sensorStatus.ph ? 'bg-gradient-to-br from-[#0f2a1f]/80 to-[#151f32]/60 border-emerald-500/25 sensor-card-online' : 'bg-gradient-to-br from-[#2a1019]/60 to-[#151f32]/60 border-rose-500/25 sensor-card-offline'">
                        <!-- Status indicator dot -->
                        <div class="absolute top-5 right-5">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                                      :class="sensorStatus.ph ? 'animate-ping bg-emerald-400' : 'animate-ping bg-rose-400'"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5"
                                      :class="sensorStatus.ph ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                            </span>
                        </div>
                        <!-- Sensor icon -->
                        <div class="sensor-icon-wrapper w-14 h-14 rounded-xl flex items-center justify-center mb-6"
                             :class="sensorStatus.ph ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-rose-500/10 border border-rose-500/30'">
                            <svg class="w-7 h-7" :class="sensorStatus.ph ? 'text-emerald-400' : 'text-rose-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611l-.772.129a18.168 18.168 0 01-15.254-4.175L5 14.5" />
                            </svg>
                        </div>
                        <!-- Sensor info -->
                        <h4 class="text-base font-bold text-white mb-1.5">pH Analog</h4>
                        <p class="text-xs text-slate-400 mb-4 leading-relaxed">Mengukur tingkat asam-basa air rendaman untuk mengontrol laju hidrolisis dioscorine.</p>
                        <div class="border-t border-slate-800/60 pt-4 mt-2 flex justify-between items-center text-[11px] text-slate-500">
                            <span>Pin Input: GPIO34</span>
                            <span class="font-semibold px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/50">ADC1_CH6</span>
                        </div>
                        <!-- Status badge -->
                        <div class="mt-4 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-xl inline-flex items-center space-x-2"
                             :class="sensorStatus.ph ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400'">
                            <span class="sensor-pulse-dot w-2 h-2 rounded-full" :class="sensorStatus.ph ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            <span x-text="sensorStatus.ph ? 'Terdeteksi (Online)' : 'Tidak Ada (Offline)'"></span>
                        </div>
                    </div>

                    <!-- Sensor 2: Turbidity Generic -->
                    <div class="sensor-card relative rounded-2xl p-6 border transition-all duration-500 cursor-default group"
                         :class="sensorStatus.turbidity ? 'bg-gradient-to-br from-[#0f2a1f]/80 to-[#151f32]/60 border-emerald-500/25 sensor-card-online' : 'bg-gradient-to-br from-[#2a1019]/60 to-[#151f32]/60 border-rose-500/25 sensor-card-offline'">
                        <div class="absolute top-5 right-5">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                                      :class="sensorStatus.turbidity ? 'animate-ping bg-emerald-400' : 'animate-ping bg-rose-400'"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5"
                                      :class="sensorStatus.turbidity ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                            </span>
                        </div>
                        <div class="sensor-icon-wrapper w-14 h-14 rounded-xl flex items-center justify-center mb-6"
                             :class="sensorStatus.turbidity ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-rose-500/10 border border-rose-500/30'">
                            <svg class="w-7 h-7" :class="sensorStatus.turbidity ? 'text-emerald-400' : 'text-rose-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-white mb-1.5">Turbidity Generic</h4>
                        <p class="text-xs text-slate-400 mb-4 leading-relaxed">Mendeteksi kekeruhan air akibat partikel pati gadung dan pelepasan racun sianida.</p>
                        <div class="border-t border-slate-800/60 pt-4 mt-2 flex justify-between items-center text-[11px] text-slate-500">
                            <span>Pin Input: GPIO35</span>
                            <span class="font-semibold px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/50">ADC1_CH7</span>
                        </div>
                        <div class="mt-4 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-xl inline-flex items-center space-x-2"
                             :class="sensorStatus.turbidity ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400'">
                            <span class="sensor-pulse-dot w-2 h-2 rounded-full" :class="sensorStatus.turbidity ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            <span x-text="sensorStatus.turbidity ? 'Terdeteksi (Online)' : 'Tidak Ada (Offline)'"></span>
                        </div>
                    </div>

                    <!-- Sensor 3: TDS -->
                    <div class="sensor-card relative rounded-2xl p-6 border transition-all duration-500 cursor-default group"
                         :class="sensorStatus.tds ? 'bg-gradient-to-br from-[#0f2a1f]/80 to-[#151f32]/60 border-emerald-500/25 sensor-card-online' : 'bg-gradient-to-br from-[#2a1019]/60 to-[#151f32]/60 border-rose-500/25 sensor-card-offline'">
                        <div class="absolute top-5 right-5">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                                      :class="sensorStatus.tds ? 'animate-ping bg-emerald-400' : 'animate-ping bg-rose-400'"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5"
                                      :class="sensorStatus.tds ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                            </span>
                        </div>
                        <div class="sensor-icon-wrapper w-14 h-14 rounded-xl flex items-center justify-center mb-6"
                             :class="sensorStatus.tds ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-rose-500/10 border border-rose-500/30'">
                            <svg class="w-7 h-7" :class="sensorStatus.tds ? 'text-emerald-400' : 'text-rose-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-white mb-1.5">TDS Sensor</h4>
                        <p class="text-xs text-slate-400 mb-4 leading-relaxed">Mengukur jumlah padatan terlarut (Total Dissolved Solids) dalam air rendaman gadung.</p>
                        <div class="border-t border-slate-800/60 pt-4 mt-2 flex justify-between items-center text-[11px] text-slate-500">
                            <span>Pin Input: GPIO32</span>
                            <span class="font-semibold px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/50">ADC1_CH4</span>
                        </div>
                        <div class="mt-4 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-xl inline-flex items-center space-x-2"
                             :class="sensorStatus.tds ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400'">
                            <span class="sensor-pulse-dot w-2 h-2 rounded-full" :class="sensorStatus.tds ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            <span x-text="sensorStatus.tds ? 'Terdeteksi (Online)' : 'Tidak Ada (Offline)'"></span>
                        </div>
                    </div>

                    <!-- Sensor 4: Suhu DS18B20 -->
                    <div class="sensor-card relative rounded-2xl p-6 border transition-all duration-500 cursor-default group"
                         :class="sensorStatus.temp ? 'bg-gradient-to-br from-[#0f2a1f]/80 to-[#151f32]/60 border-emerald-500/25 sensor-card-online' : 'bg-gradient-to-br from-[#2a1019]/60 to-[#151f32]/60 border-rose-500/25 sensor-card-offline'">
                        <div class="absolute top-5 right-5">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                                      :class="sensorStatus.temp ? 'animate-ping bg-emerald-400' : 'animate-ping bg-rose-400'"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5"
                                      :class="sensorStatus.temp ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                            </span>
                        </div>
                        <div class="sensor-icon-wrapper w-14 h-14 rounded-xl flex items-center justify-center mb-6"
                             :class="sensorStatus.temp ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-rose-500/10 border border-rose-500/30'">
                            <svg class="w-7 h-7" :class="sensorStatus.temp ? 'text-emerald-400' : 'text-rose-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-white mb-1.5">Suhu DS18B20</h4>
                        <p class="text-xs text-slate-400 mb-4 leading-relaxed">Sensor suhu digital waterproof (One-wire) untuk kompensasi suhu pembacaan sensor lain.</p>
                        <div class="border-t border-slate-800/60 pt-4 mt-2 flex justify-between items-center text-[11px] text-slate-500">
                            <span>Pin Input: GPIO4</span>
                            <span class="font-semibold px-2 py-0.5 rounded bg-slate-800/80 border border-slate-700/50">One-Wire</span>
                        </div>
                        <div class="mt-4 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-xl inline-flex items-center space-x-2"
                             :class="sensorStatus.temp ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400'">
                            <span class="sensor-pulse-dot w-2 h-2 rounded-full" :class="sensorStatus.temp ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            <span x-text="sensorStatus.temp ? 'Terdeteksi (Online)' : 'Tidak Ada (Offline)'"></span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- REAL-TIME SENSOR READINGS PANEL                       -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <section class="bg-[#151f32]/60 backdrop-blur-md border border-[#233554] rounded-2xl p-8 relative overflow-hidden">
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="bg-violet-500/10 p-3 rounded-xl border border-violet-500/30">
                            <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v5.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 013 18.375v-5.25zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125v-9.75zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v14.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white tracking-wide">Pembacaan Sensor Real-Time</h2>
                            <p class="text-sm text-slate-400 mt-1">Nilai pembacaan terbaru dari modul sensor yang terhubung ke device</p>
                        </div>
                    </div>

                    <!-- Last updated + auto-refresh badge -->
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2 bg-[#0c1322] border border-[#233554] px-3 py-2 rounded-xl text-xs text-slate-400">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Update terakhir: </span>
                            <span class="text-white font-medium" x-text="lastUpdatedTime">—</span>
                        </div>
                        <div class="flex items-center space-x-1.5 text-xs" :class="isPolling ? 'text-emerald-400' : 'text-slate-500'">
                            <span class="relative flex h-2 w-2">
                                <span :class="isPolling ? 'animate-ping bg-emerald-400' : 'bg-slate-600'" class="absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                                <span :class="isPolling ? 'bg-emerald-500' : 'bg-slate-600'" class="relative inline-flex rounded-full h-2 w-2"></span>
                            </span>
                            <span x-text="isPolling ? 'Auto-refresh 5s' : 'Paused'"></span>
                        </div>
                    </div>
                </div>

                <!-- Safety Status Hero Badge -->
                <div class="mb-8 flex flex-col sm:flex-row items-center justify-between bg-[#0c1322]/80 border border-[#233554] rounded-2xl p-6 gap-4">
                    <div class="flex items-center space-x-5">
                        <!-- Large status icon -->
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 transition-all duration-500"
                             :class="safetyStatusColor.bgIcon">
                            <template x-if="latestSafetyStatus === 'Aman'">
                                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                            </template>
                            <template x-if="latestSafetyStatus === 'Proses'">
                                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M21.015 4.356v4.992" />
                                </svg>
                            </template>
                            <template x-if="latestSafetyStatus === 'Bahaya'">
                                <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </template>
                            <template x-if="latestSafetyStatus === '—'">
                                <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                            </template>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Status Keamanan Air</p>
                            <h3 class="text-2xl font-extrabold tracking-tight transition-colors duration-500"
                                :class="safetyStatusColor.text"
                                x-text="latestSafetyStatus"></h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="safetyStatusDescription"></p>
                        </div>
                    </div>
                    <!-- HCN Estimation standalone badge -->
                    <div class="text-center sm:text-right bg-[#151f32] border border-[#233554] rounded-xl px-6 py-4">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">HCN Estimasi</p>
                        <p class="text-3xl font-extrabold tabular-nums transition-colors duration-500"
                           :class="getHcnColor(latestValues.hcn)"
                           x-text="latestValues.hcn !== null ? latestValues.hcn.toFixed(4) : '—'"></p>
                        <p class="text-[10px] text-slate-500 mt-0.5">mg/L</p>
                    </div>
                </div>

                <!-- Sensor Values Grid (5 columns on lg, 2 on sm) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    <!-- pH Value Card -->
                    <div class="relative bg-[#0c1322]/80 border border-[#233554] rounded-2xl p-5 hover:border-[#314b77] transition-all duration-300 group overflow-hidden">
                        <!-- Decorative gradient bar at top -->
                        <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl transition-all duration-500"
                             :class="getPhBarColor(latestValues.ph)"></div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-500/10 border border-blue-500/25">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A18.168 18.168 0 013.562 14.44L5 14.5" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">pH</span>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg uppercase tracking-wider"
                                  :class="getPhBadge(latestValues.ph).class"
                                  x-text="getPhBadge(latestValues.ph).label"></span>
                        </div>
                        <p class="text-4xl font-extrabold tabular-nums transition-colors duration-500"
                           :class="getPhTextColor(latestValues.ph)"
                           x-text="latestValues.ph !== null ? latestValues.ph.toFixed(2) : '—'"></p>
                        <div class="mt-3 flex justify-between items-center text-[10px] text-slate-600">
                            <span>Rentang aman: 6.5 – 7.5</span>
                            <span>Skala 0–14</span>
                        </div>
                        <!-- Mini progress bar -->
                        <div class="mt-2 h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 :class="getPhBarColor(latestValues.ph)"
                                 :style="'width: ' + (latestValues.ph !== null ? (latestValues.ph / 14 * 100) : 0) + '%'"></div>
                        </div>
                    </div>

                    <!-- Turbidity Value Card -->
                    <div class="relative bg-[#0c1322]/80 border border-[#233554] rounded-2xl p-5 hover:border-[#314b77] transition-all duration-300 group overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl transition-all duration-500"
                             :class="getTurbBarColor(latestValues.turbidity)"></div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-cyan-500/10 border border-cyan-500/25">
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kekeruhan</span>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg uppercase tracking-wider"
                                  :class="getTurbBadge(latestValues.turbidity).class"
                                  x-text="getTurbBadge(latestValues.turbidity).label"></span>
                        </div>
                        <div class="flex items-baseline space-x-1.5">
                            <p class="text-4xl font-extrabold tabular-nums transition-colors duration-500"
                               :class="getTurbTextColor(latestValues.turbidity)"
                               x-text="latestValues.turbidity !== null ? latestValues.turbidity.toFixed(1) : '—'"></p>
                            <span class="text-sm font-medium text-slate-500">NTU</span>
                        </div>
                        <div class="mt-3 flex justify-between items-center text-[10px] text-slate-600">
                            <span>Aman: &lt; 100 NTU</span>
                            <span>Bahaya: &gt; 600 NTU</span>
                        </div>
                        <div class="mt-2 h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 :class="getTurbBarColor(latestValues.turbidity)"
                                 :style="'width: ' + (latestValues.turbidity !== null ? Math.min(latestValues.turbidity / 800 * 100, 100) : 0) + '%'"></div>
                        </div>
                    </div>

                    <!-- TDS Value Card -->
                    <div class="relative bg-[#0c1322]/80 border border-[#233554] rounded-2xl p-5 hover:border-[#314b77] transition-all duration-300 group overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl transition-all duration-500"
                             :class="getTdsBarColor(latestValues.tds)"></div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-500/10 border border-amber-500/25">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">TDS</span>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg uppercase tracking-wider"
                                  :class="getTdsBadge(latestValues.tds).class"
                                  x-text="getTdsBadge(latestValues.tds).label"></span>
                        </div>
                        <div class="flex items-baseline space-x-1.5">
                            <p class="text-4xl font-extrabold tabular-nums transition-colors duration-500"
                               :class="getTdsTextColor(latestValues.tds)"
                               x-text="latestValues.tds !== null ? latestValues.tds.toFixed(1) : '—'"></p>
                            <span class="text-sm font-medium text-slate-500">ppm</span>
                        </div>
                        <div class="mt-3 flex justify-between items-center text-[10px] text-slate-600">
                            <span>Aman: &lt; 150 ppm</span>
                            <span>Bahaya: &gt; 700 ppm</span>
                        </div>
                        <div class="mt-2 h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 :class="getTdsBarColor(latestValues.tds)"
                                 :style="'width: ' + (latestValues.tds !== null ? Math.min(latestValues.tds / 900 * 100, 100) : 0) + '%'"></div>
                        </div>
                    </div>

                    <!-- Temperature Value Card -->
                    <div class="relative bg-[#0c1322]/80 border border-[#233554] rounded-2xl p-5 hover:border-[#314b77] transition-all duration-300 group overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl bg-gradient-to-r from-sky-500 to-orange-500"></div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-orange-500/10 border border-orange-500/25">
                                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Suhu</span>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg uppercase tracking-wider bg-slate-800/60 border border-slate-700/50 text-slate-300">MONITORING</span>
                        </div>
                        <div class="flex items-baseline space-x-1.5">
                            <p class="text-4xl font-extrabold tabular-nums text-white"
                               x-text="latestValues.temp !== null ? latestValues.temp.toFixed(1) : '—'"></p>
                            <span class="text-sm font-medium text-slate-500">°C</span>
                        </div>
                        <div class="mt-3 flex justify-between items-center text-[10px] text-slate-600">
                            <span>Kompensasi kalibrasi sensor</span>
                            <span>DS18B20</span>
                        </div>
                        <div class="mt-2 h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-orange-500 transition-all duration-700"
                                 :style="'width: ' + (latestValues.temp !== null ? Math.min(Math.max((latestValues.temp - 15) / 30 * 100, 5), 100) : 0) + '%'"></div>
                        </div>
                    </div>

                </div>

                <!-- Readings History Table (last 10 entries) -->
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Riwayat Pembacaan Terbaru</h3>
                        <span class="text-xs text-slate-500" x-text="'Menampilkan ' + Math.min(logs.length, 10) + ' entri terakhir'"></span>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-[#233554]">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#0c1322] text-left">
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Waktu</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">pH</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Kekeruhan (NTU)</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">TDS (ppm)</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Suhu (°C)</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">HCN Est.</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#1e293b]">
                                <template x-for="(log, idx) in logs.slice(-10).reverse()" :key="idx">
                                    <tr class="hover:bg-[#151f32]/60 transition-colors duration-150">
                                        <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap font-mono" x-text="formatLogTime(log.created_at)"></td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold tabular-nums" :class="getPhTextColor(log.ph_value)" x-text="parseFloat(log.ph_value).toFixed(2)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold tabular-nums" :class="getTurbTextColor(log.turbidity_value)" x-text="parseFloat(log.turbidity_value).toFixed(1)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold tabular-nums" :class="getTdsTextColor(log.tds_value)" x-text="parseFloat(log.tds_value).toFixed(1)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-white font-bold tabular-nums" x-text="parseFloat(log.temperature_value).toFixed(1)"></td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold tabular-nums" :class="getHcnColor(parseFloat(log.hcn_estimated))" x-text="log.hcn_estimated ? parseFloat(log.hcn_estimated).toFixed(4) : '—'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg inline-block"
                                                  :class="{
                                                      'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400': log.safety_status === 'Aman',
                                                      'bg-amber-500/10 border border-amber-500/30 text-amber-400': log.safety_status === 'Proses',
                                                      'bg-rose-500/10 border border-rose-500/30 text-rose-400': log.safety_status === 'Bahaya'
                                                  }"
                                                  x-text="log.safety_status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="logs.length === 0">
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-slate-500 text-sm">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                            </svg>
                                            Belum ada data sensor. Pastikan device ESP32 terhubung dan mengirim data.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Alpine JS Application Logic -->
    <script>
        function sensoryApp() {
            return {
                devices: @json($devices),
                currentDevice: @json($currentDevice),
                logs: @json($logs),
                
                isPolling: true,
                pollingInterval: null,
                sidebarOpen: false,
                
                // Track online state of the whole device
                deviceOnline: true,
                serverTime: "{{ now()->toIso8601String() }}",
                
                sensorStatus: {
                    ph: true,
                    turbidity: true,
                    tds: true,
                    temp: true
                },

                // Real-time sensor values from latest log
                latestValues: {
                    ph: null,
                    turbidity: null,
                    tds: null,
                    temp: null,
                    hcn: null,
                },
                latestSafetyStatus: '—',
                lastUpdatedTime: '—',

                init() {
                    this.updateSensorStatus();
                    this.updateLatestValues();
                    this.startPolling();
                },

                updateSensorStatus() {
                    if (this.logs && this.logs.length > 0) {
                        const lastLog = this.logs[this.logs.length - 1];
                        
                        // Compare last log timestamp against the serverTime
                        const lastLogTime = new Date(lastLog.created_at).getTime();
                        const sTime = new Date(this.serverTime).getTime();
                        
                        // If the gap is less than 15 seconds, device is online
                        // Since ESP32 sends data every 5 seconds, a 15-second gap indicates it is offline.
                        this.deviceOnline = (sTime - lastLogTime) < 15000;
                        
                        if (this.deviceOnline) {
                            // Update status keberadaan sensor from database
                            this.sensorStatus.ph        = lastLog.sensor_ph_detected !== undefined        ? !!lastLog.sensor_ph_detected        : true;
                            this.sensorStatus.turbidity = lastLog.sensor_turbidity_detected !== undefined ? !!lastLog.sensor_turbidity_detected : true;
                            this.sensorStatus.tds       = lastLog.sensor_tds_detected !== undefined       ? !!lastLog.sensor_tds_detected       : true;
                            this.sensorStatus.temp      = lastLog.sensor_temp_detected !== undefined      ? !!lastLog.sensor_temp_detected      : true;
                        } else {
                            // If device is offline, all individual sensors are unreachable / offline
                            this.sensorStatus.ph        = false;
                            this.sensorStatus.turbidity = false;
                            this.sensorStatus.tds       = false;
                            this.sensorStatus.temp      = false;
                        }
                    } else {
                        this.deviceOnline = false;
                        this.sensorStatus.ph        = false;
                        this.sensorStatus.turbidity = false;
                        this.sensorStatus.tds       = false;
                        this.sensorStatus.temp      = false;
                    }
                },

                updateLatestValues() {
                    if (this.logs && this.logs.length > 0) {
                        const lastLog = this.logs[this.logs.length - 1];
                        this.latestValues.ph        = lastLog.ph_value !== undefined        ? parseFloat(lastLog.ph_value)          : null;
                        this.latestValues.turbidity = lastLog.turbidity_value !== undefined ? parseFloat(lastLog.turbidity_value)   : null;
                        this.latestValues.tds       = lastLog.tds_value !== undefined       ? parseFloat(lastLog.tds_value)         : null;
                        this.latestValues.temp      = lastLog.temperature_value !== undefined ? parseFloat(lastLog.temperature_value) : null;
                        this.latestValues.hcn       = lastLog.hcn_estimated !== undefined && lastLog.hcn_estimated !== null ? parseFloat(lastLog.hcn_estimated) : null;
                        this.latestSafetyStatus     = this.deviceOnline ? (lastLog.safety_status || '—') : 'Offline';
                        this.lastUpdatedTime        = this.formatLogTime(lastLog.created_at);
                    } else {
                        this.latestValues = { ph: null, turbidity: null, tds: null, temp: null, hcn: null };
                        this.latestSafetyStatus = 'Offline';
                        this.lastUpdatedTime = '—';
                    }
                },

                selectDevice(device) {
                    this.currentDevice = device;
                    this.fetchDeviceData();
                },

                fetchDeviceData() {
                    fetch(`/devices/${this.currentDevice.id}/data`)
                        .then(res => res.json())
                        .then(data => {
                            this.logs = data.logs;
                            this.serverTime = data.server_time || new Date().toISOString();
                            this.updateSensorStatus();
                            this.updateLatestValues();
                        });
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

                getSensorOnlineCount() {
                    let count = 0;
                    if (this.sensorStatus.ph) count++;
                    if (this.sensorStatus.turbidity) count++;
                    if (this.sensorStatus.tds) count++;
                    if (this.sensorStatus.temp) count++;
                    return count;
                },

                // ── Format timestamp ──
                formatLogTime(timestamp) {
                    if (!timestamp) return '—';
                    const d = new Date(timestamp);
                    const pad = n => String(n).padStart(2, '0');
                    return `${pad(d.getDate())}/${pad(d.getMonth()+1)} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
                },

                // ── Safety Status color/description ──
                get safetyStatusColor() {
                    switch (this.latestSafetyStatus) {
                        case 'Aman':   return { text: 'text-emerald-400', bgIcon: 'bg-emerald-500/10 border border-emerald-500/30' };
                        case 'Proses': return { text: 'text-amber-400',   bgIcon: 'bg-amber-500/10 border border-amber-500/30' };
                        case 'Bahaya': return { text: 'text-rose-400',    bgIcon: 'bg-rose-500/10 border border-rose-500/30' };
                        default:       return { text: 'text-slate-500',   bgIcon: 'bg-slate-800/60 border border-slate-700/50' };
                    }
                },
                get safetyStatusDescription() {
                    switch (this.latestSafetyStatus) {
                        case 'Aman':   return 'Semua parameter dalam batas aman. Gadung siap diolah lebih lanjut.';
                        case 'Proses': return 'Detoksifikasi sedang berjalan. Lanjutkan perendaman dan pantau berkala.';
                        case 'Bahaya': return 'Parameter melebihi batas kritis! Segera ganti air rendaman.';
                        default:       return 'Menunggu data dari device sensor...';
                    }
                },

                // ── pH Helpers ──
                getPhTextColor(v) {
                    if (v === null || v === undefined) return 'text-slate-500';
                    v = parseFloat(v);
                    if (v < 5.5 || v > 9.0) return 'text-rose-400';
                    if (v < 6.5 || v > 7.5) return 'text-amber-400';
                    return 'text-emerald-400';
                },
                getPhBarColor(v) {
                    if (v === null || v === undefined) return 'bg-slate-700';
                    v = parseFloat(v);
                    if (v < 5.5 || v > 9.0) return 'bg-gradient-to-r from-rose-500 to-rose-400';
                    if (v < 6.5 || v > 7.5) return 'bg-gradient-to-r from-amber-500 to-amber-400';
                    return 'bg-gradient-to-r from-emerald-500 to-emerald-400';
                },
                getPhBadge(v) {
                    if (v === null || v === undefined) return { class: 'bg-slate-800/60 border border-slate-700/50 text-slate-500', label: 'N/A' };
                    v = parseFloat(v);
                    if (v < 5.5 || v > 9.0) return { class: 'bg-rose-500/10 border border-rose-500/30 text-rose-400', label: 'BAHAYA' };
                    if (v < 6.5 || v > 7.5) return { class: 'bg-amber-500/10 border border-amber-500/30 text-amber-400', label: 'PROSES' };
                    return { class: 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400', label: 'AMAN' };
                },

                // ── Turbidity Helpers ──
                getTurbTextColor(v) {
                    if (v === null || v === undefined) return 'text-slate-500';
                    v = parseFloat(v);
                    if (v > 600) return 'text-rose-400';
                    if (v > 100) return 'text-amber-400';
                    return 'text-emerald-400';
                },
                getTurbBarColor(v) {
                    if (v === null || v === undefined) return 'bg-slate-700';
                    v = parseFloat(v);
                    if (v > 600) return 'bg-gradient-to-r from-rose-500 to-rose-400';
                    if (v > 100) return 'bg-gradient-to-r from-amber-500 to-amber-400';
                    return 'bg-gradient-to-r from-emerald-500 to-emerald-400';
                },
                getTurbBadge(v) {
                    if (v === null || v === undefined) return { class: 'bg-slate-800/60 border border-slate-700/50 text-slate-500', label: 'N/A' };
                    v = parseFloat(v);
                    if (v > 600) return { class: 'bg-rose-500/10 border border-rose-500/30 text-rose-400', label: 'BAHAYA' };
                    if (v > 100) return { class: 'bg-amber-500/10 border border-amber-500/30 text-amber-400', label: 'PROSES' };
                    return { class: 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400', label: 'AMAN' };
                },

                // ── TDS Helpers ──
                getTdsTextColor(v) {
                    if (v === null || v === undefined) return 'text-slate-500';
                    v = parseFloat(v);
                    if (v > 700) return 'text-rose-400';
                    if (v > 150) return 'text-amber-400';
                    return 'text-emerald-400';
                },
                getTdsBarColor(v) {
                    if (v === null || v === undefined) return 'bg-slate-700';
                    v = parseFloat(v);
                    if (v > 700) return 'bg-gradient-to-r from-rose-500 to-rose-400';
                    if (v > 150) return 'bg-gradient-to-r from-amber-500 to-amber-400';
                    return 'bg-gradient-to-r from-emerald-500 to-emerald-400';
                },
                getTdsBadge(v) {
                    if (v === null || v === undefined) return { class: 'bg-slate-800/60 border border-slate-700/50 text-slate-500', label: 'N/A' };
                    v = parseFloat(v);
                    if (v > 700) return { class: 'bg-rose-500/10 border border-rose-500/30 text-rose-400', label: 'BAHAYA' };
                    if (v > 150) return { class: 'bg-amber-500/10 border border-amber-500/30 text-amber-400', label: 'PROSES' };
                    return { class: 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400', label: 'AMAN' };
                },

                // ── HCN Helpers ──
                getHcnColor(v) {
                    if (v === null || v === undefined || isNaN(v)) return 'text-slate-500';
                    if (v > 3.0) return 'text-rose-400';
                    if (v >= 0.5) return 'text-amber-400';
                    return 'text-emerald-400';
                },
            }
        }
    </script>
</body>
</html>
