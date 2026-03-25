@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-[#0f172a] overflow-hidden flex items-center justify-center font-sans antialiased text-white selection:bg-amber-500 selection:text-white">
    <!-- Gradient Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-[#0f172a] to-slate-900"></div>
    
    <!-- Animated Blobs -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>

    <div class="relative z-10 max-w-4xl w-full px-6 py-12">
        <div class="glass-container p-8 md:p-16 rounded-[2.5rem] border border-white/10 bg-white/5 backdrop-blur-2xl shadow-2xl text-center transform transition-all duration-700 hover:scale-[1.01]">
            
            <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm font-semibold mb-8 tracking-wide uppercase">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>Task Manager Elite</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 bg-clip-text text-transparent bg-gradient-to-r from-white via-white to-white/60">
                Organiza tu Vida <br class="hidden md:block"> con <span class="text-amber-500">ToDoList</span>
            </h1>

            <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto mb-12 leading-relaxed">
                Potencia tu productividad con una plataforma diseñada para el enfoque total. Tus tareas, tus metas, tu control.
            </p>

            <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                @auth
                    <a href="/admin" class="group relative px-8 py-4 bg-amber-500 text-[#0f172a] font-bold rounded-2xl transition-all duration-300 hover:bg-amber-400 hover:shadow-[0_0_40px_rgba(245,158,11,0.3)] active:scale-95 flex items-center">
                        Ir al Panel de Tareas
                        <svg class="w-5 h-5 ml-2 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                @else
                    <a href="/admin/login" class="px-8 py-4 bg-white/10 hover:bg-white/15 border border-white/10 rounded-2xl font-bold transition-all backdrop-blur-sm">
                        Iniciar Sesión
                    </a>
                    <a href="/admin/register" class="px-8 py-4 bg-amber-500 text-[#0f172a] font-bold rounded-2xl transition-all hover:bg-amber-400">
                        Comenzar Gratis
                    </a>
                @endauth
            </div>

            <div class="mt-16 pt-8 border-t border-white/5 grid grid-cols-3 gap-8 text-slate-500 text-sm font-medium">
                <div>
                    <span class="block text-white text-xl font-bold mb-1">+10k</span>
                    Tareas diarias
                </div>
                <div>
                    <span class="block text-white text-xl font-bold mb-1">99.9%</span>
                    Disponibilidad
                </div>
                <div>
                    <span class="block text-white text-xl font-bold mb-1">Elite</span>
                    Diseño UX
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-container {
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    }
</style>
@endsection
