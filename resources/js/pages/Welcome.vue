<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { PlayCircle, Award, Zap, ChevronRight } from 'lucide-vue-next';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Aprende inglés viendo películas" />

    <div class="min-h-screen bg-gray-900 text-white selection:bg-yellow-400 selection:text-black overflow-x-hidden font-sans">
        
        <nav class="absolute top-0 w-full z-50 p-6">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2 group cursor-default">
                    <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center shadow-[0_0_15px_rgba(250,204,21,0.4)] group-hover:scale-110 transition duration-300">
                        <span class="text-xl font-black text-black">F</span>
                    </div>
                    <span class="font-bold text-xl tracking-tight ml-1">Flick</span>
                </div>

                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-bold text-gray-300 hover:text-white transition">
                        Ir al Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-bold text-gray-300 hover:text-white transition hidden sm:block">
                            Iniciar Sesión
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-5 py-2 rounded-full text-sm font-bold backdrop-blur-md transition hover:scale-105">
                            Registrarse
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <div class="relative min-h-screen flex items-center justify-center">
            
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-yellow-500/20 blur-[150px] rounded-full pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 blur-[150px] rounded-full pointer-events-none"></div>
            
            <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-400/10 border border-yellow-400/20 text-yellow-400 text-xs font-bold uppercase tracking-wider mb-6 animate-fade-in-up">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
                    Nuevo Método 2026
                </div>

                <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-8 leading-tight">
                    Aprende inglés <br class="hidden md:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-200">viendo películas.</span>
                </h1>

                <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Olvídate de los libros aburridos. Mejora tu vocabulario y comprensión auditiva con clips de tus películas favoritas y quizzes interactivos.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Link :href="route('register')" class="group relative px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-black font-black text-lg rounded-full shadow-[0_0_30px_rgba(250,204,21,0.3)] transition transform hover:scale-105 hover:-translate-y-1 flex items-center gap-2">
                        EMPEZAR GRATIS
                        <ChevronRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" stroke-width="3" />
                    </Link>
                    <a href="#como-funciona" class="px-8 py-4 bg-gray-800 hover:bg-gray-700 text-white font-bold text-lg rounded-full border border-gray-700 transition flex items-center gap-2">
                        <PlayCircle class="w-5 h-5" />
                        Ver Demo
                    </a>
                </div>

                <div class="mt-16 grid grid-cols-2 md:grid-cols-3 gap-8 max-w-3xl mx-auto opacity-70">
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-bold text-white">+100</span>
                        <span class="text-xs text-gray-500 uppercase tracking-widest">Películas</span>
                    </div> 
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-bold text-white">A1-C2</span>
                        <span class="text-xs text-gray-500 uppercase tracking-widest">Niveles</span>
                    </div>
                    <div class="col-span-2 md:col-span-1 flex flex-col items-center">
                        <span class="text-3xl font-bold text-white">Free</span>
                        <span class="text-xs text-gray-500 uppercase tracking-widest">Para siempre</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="como-funciona" class="py-24 bg-black/50 border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-3xl bg-gray-900 border border-gray-800 hover:border-yellow-500/30 transition group">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-400 mb-6 group-hover:scale-110 transition">
                            <PlayCircle size="28" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Clips Reales</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Nada de audios robóticos. Escucha acentos reales, jerga (slang) y entonación natural de actores de Hollywood.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-900 border border-gray-800 hover:border-yellow-500/30 transition group">
                        <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-400 mb-6 group-hover:scale-110 transition">
                            <Award size="28" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Gamificación</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Gana XP, sube de nivel y mantén tu racha. Aprender inglés se convierte en un juego adictivo.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-900 border border-gray-800 hover:border-yellow-500/30 transition group">
                        <div class="w-12 h-12 bg-yellow-500/10 rounded-2xl flex items-center justify-center text-yellow-400 mb-6 group-hover:scale-110 transition">
                            <Zap size="28" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Feedback Instantáneo</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Responde quizzes en tiempo real y recibe correcciones inmediatas. Entrena tu oído sin frustraciones.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <footer class="py-12 text-center text-gray-600 text-sm border-t border-gray-800 bg-gray-900">
            <p>&copy; 2026 Flick Learning. Hecho con 💛 y Laravel.</p>
        </footer>
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>