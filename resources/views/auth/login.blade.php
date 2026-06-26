<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - gadungGuard</title>
    
    <!-- Tailwind CSS (via Vite bundle) -->
    @vite(['resources/css/app.css'])

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0c1322;
        }
    </style>
</head>
<body class="text-slate-100 flex items-center justify-center min-h-screen p-4 overflow-hidden relative">

    <!-- Abstract background glow elements -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] -z-10 animate-pulse"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-[120px] -z-10 animate-pulse" style="animation-delay: 2s;"></div>

    <!-- Login Card Wrapper -->
    <div class="w-full max-w-md bg-[#151f32]/65 backdrop-blur-xl border border-[#233554] p-8 rounded-2xl shadow-2xl z-10 transition duration-300 hover:border-[#314b77]">
        
        <!-- Header / Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-emerald-500/10 p-3 rounded-2xl border border-emerald-500/30 mb-3 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-200 bg-clip-text text-transparent">gadungGuard</h1>
            <p class="text-slate-400 text-xs mt-1.5 font-medium tracking-wide uppercase">Admin Authentication</p>
        </div>

        <!-- Validation Errors Box -->
        @if ($errors->any())
            <div class="mb-6 bg-rose-500/10 border border-rose-500/30 text-rose-200 p-4 rounded-xl text-sm flex items-start space-x-3.5">
                <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <div class="space-y-1">
                    <p class="font-semibold text-rose-300">Login Failed</p>
                    <ul class="list-disc list-inside text-xs text-rose-200/80">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Login Form -->
        <form action="/login" method="POST" class="space-y-5">
            @csrf
            
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required 
                        value="{{ old('email') }}"
                        placeholder="admin@gadungguard.com"
                        class="w-full bg-[#0c1322] border border-[#233554] rounded-xl py-3 pl-11 pr-4 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition duration-200">
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        placeholder="••••••••"
                        class="w-full bg-[#0c1322] border border-[#233554] rounded-xl py-3 pl-11 pr-4 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition duration-200">
                </div>
            </div>

            <!-- Remember me checkbox -->
            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 text-sm text-slate-400 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 bg-[#0c1322] border-[#233554] rounded text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 focus:ring-offset-transparent cursor-pointer">
                    <span>Keep me logged in</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold py-3.5 rounded-xl border border-transparent transition duration-200 active:scale-[0.98] shadow-lg shadow-emerald-500/15">
                Sign In
            </button>
        </form>

        <!-- Credentials Helper Info Box -->
        <div class="mt-8 border-t border-[#233554]/50 pt-5">
            <div class="bg-emerald-500/5 border border-emerald-500/15 text-emerald-300/90 p-4 rounded-xl text-xs space-y-1">
                <span class="font-bold flex items-center space-x-1.5 text-emerald-400">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span>Default Admin Account</span>
                </span>
                <p class="mt-1">Use these credentials for immediate testing:</p>
                <div class="mt-2 space-y-0.5 font-mono text-slate-300">
                    <p>Email: <span class="text-white select-all">admin@gadungguard.com</span></p>
                    <p>Pass: <span class="text-white select-all">password</span></p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
