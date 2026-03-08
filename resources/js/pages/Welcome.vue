<script setup>
import { Link } from '@inertiajs/vue3';
import { Play, Brain, Subtitles, ArrowRight, Zap, Heart, Volume2 } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const currentSlide = ref(0);
const slides = [
    {
        image: '/images/prey.png',
        word: "You're ",
        phonetic: "/ˈʊər/",
        translation: "Eres",
        fullText: "the prey.",
        color: "text-yellow-400"
    },
    {
        image: '/images/madmax.png', 
        word: "Everyone ",
        phonetic: "/ˈɛv.ri.wʌn/", 
        translation: "todos los demás",
        fullText: "Me or everyone else.",
        color: "text-yellow-400" 
    },
    {
        image: '/images/tron-ares.png', 
        word: "Control ",
        phonetic: "/kən.ˈtroʊl/", 
        translation: "dominio",
        fullText: "of this?",
        prefixText: "You think you're in ",
        color: "text-yellow-400"
    },
    {
        image: '/images/madmax-2.png', 
        word: "Property ",
        phonetic: "/ˈprɑ.pɚ.ti/", 
        translation: "posesión",
        fullText: "property.",
        prefixText: "I want them back, they're not in ",
        color: "text-yellow-400"
    },
    
    
];

onMounted(() => {
    setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.length;
    }, 4000); // Cambia cada 4 segundos
});
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white font-sans selection:bg-yellow-400 selection:text-black overflow-x-hidden">
        
        <nav class="absolute top-0 w-full z-50 px-6 py-6 flex justify-between items-center max-w-7xl mx-auto left-0 right-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(250,204,21,0.3)]">
                    <span class="text-lg font-black text-black">F</span>
                </div>
                <span class="font-bold text-xl tracking-tight">Flick</span>
            </div>
            <div class="flex gap-4 items-center">
                <Link :href="route('login')" class="text-sm font-medium text-gray-300 hover:text-white transition">Iniciar sesión</Link>
                <Link :href="route('register')" class="text-sm font-bold bg-white text-black px-4 py-2 rounded-full hover:bg-gray-200 transition shadow-lg">Registrarse</Link>
            </div>
        </nav>

        <main class="relative pt-32 pb-16 sm:pt-40 sm:pb-24 lg:pb-32 px-6 max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12 lg:gap-8">
            
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 lg:translate-x-0 lg:left-0 w-96 h-96 bg-yellow-500/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="flex-1 text-center lg:text-left z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-400/10 border border-yellow-400/20 text-yellow-400 text-xs font-bold tracking-wide uppercase mb-6">
                    <Zap size="14" class="fill-yellow-400" /> Método con IA 2026
                </div>
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight mb-6 leading-[1.1]">
                    Aprende inglés <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-yellow-600">viendo películas.</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-400 mb-10 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Olvídate de los libros aburridos. Mejora tu vocabulario y comprensión auditiva con clips reales de cine y quizzes generados por Inteligencia Artificial.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a :href="route('google.login')" class="w-full sm:w-auto flex items-center justify-center gap-3 bg-yellow-400 text-black font-bold text-lg px-8 py-4 rounded-full hover:bg-yellow-300 hover:scale-105 transition-all shadow-[0_0_30px_rgba(250,204,21,0.3)]">
                        Empezar Gratis <ArrowRight size="20" />
                    </a>
                </div>

                <div class="mt-12 flex items-center justify-center lg:justify-start gap-8 border-t border-gray-800 pt-8">
                    <div>
                        <div class="text-2xl font-black text-white">+100</div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Películas</div>
                    </div>
                    <div class="w-px h-8 bg-gray-800"></div>
                    <div>
                        <div class="text-2xl font-black text-white">A1-C2</div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Niveles</div>
                    </div>
                    <div class="w-px h-8 bg-gray-800"></div>
                    <div>
                        <div class="text-2xl font-black text-white">100%</div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Práctico</div>
                    </div>
                </div>
            </div>

            <div class="flex-1 w-full max-w-lg z-10 relative mt-8 lg:mt-0">
                <div class="aspect-[4/5] bg-gray-900 rounded-3xl border border-gray-700 overflow-hidden shadow-2xl relative shadow-yellow-900/10">
                    
                    <transition-group 
                        enter-active-class="transition duration-700 ease-out transform"
                        enter-from-class="translate-y-full opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition duration-700 ease-in transform absolute inset-0"
                        leave-from-class="translate-y-0 opacity-100"
                        leave-to-class="-translate-y-full opacity-0"
                    >
                        <div v-for="(slide, index) in [slides[currentSlide]]" :key="currentSlide" class="absolute inset-0">
                            <img :src="slide.image" class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent opacity-80"></div>

                            <div class="absolute bottom-16 w-full text-center px-8 z-20">
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-6 w-56 bg-gray-900/95 backdrop-blur-xl border border-gray-700 rounded-xl p-4 text-left shadow-2xl shadow-black/50">
                                    <h3 class="text-white font-bold text-lg leading-none mb-1">{{ slide.word }}</h3>
                                    <p class="text-gray-400 text-[10px] font-mono mb-2 uppercase tracking-widest">{{ slide.phonetic }}</p>
                                    <div class="border-t border-gray-700/50 pt-2">
                                        <p class="text-yellow-400 font-bold text-base leading-tight">{{ slide.translation }}</p>
                                    </div>
                                    <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-gray-900 border-b border-r border-gray-700 rotate-45"></div>
                                </div>

                                <div class="text-2xl font-medium leading-tight">
                                    <span class="text-white drop-shadow-md">{{ slide.prefixText }}</span>
                                    <span :class="['font-bold drop-shadow-lg', slide.color]">{{ slide.word }}</span> 
                                    <span class="text-white drop-shadow-lg">{{ slide.fullText }}</span>
                                </div>
                            </div>
                        </div>
                    </transition-group>

                    <div class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col gap-4 z-30">
                        <div class="w-10 h-10 rounded-full bg-gray-900/60 backdrop-blur-md border border-white/10 flex items-center justify-center text-yellow-400 shadow-lg">
                            <Brain size="20" />
                        </div>
                        
                        <div class="w-10 h-10 rounded-full bg-gray-900/60 backdrop-blur-md border border-white/10 flex items-center justify-center text-white shadow-lg">
                            <Heart size="20" />
                        </div>

                        <div class="w-10 h-10 rounded-full bg-gray-900/60 backdrop-blur-md border border-white/10 flex items-center justify-center text-white shadow-lg">
                            <Volume2 size="20" />
                        </div>

                        <div class="w-10 h-10 rounded-full bg-gray-900/60 backdrop-blur-md border border-white/10 flex items-center justify-center text-white shadow-lg font-bold">
                            <Subtitles size="20" />
                        </div>
                    </div>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-30 opacity-60">
                        <div class="flex flex-col items-center gap-1 animate-bounce">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                            <span class="text-[9px] font-black uppercase tracking-widest">Feed IA</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <section class="bg-gray-900 border-t border-gray-800 py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">Aprende sin darte cuenta</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">Un flujo diseñado para mantenerte inmerso en la historia mientras tu cerebro absorbe nuevo vocabulario.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-gray-800/50 p-8 rounded-2xl border border-gray-700 hover:border-yellow-500/50 transition duration-300">
                        <div class="w-12 h-12 bg-blue-500/20 text-blue-400 rounded-xl flex items-center justify-center mb-6">
                            <Play size="24" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">1. Mira y Escucha</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Disfruta de escenas icónicas de películas y series con subtítulos en inglés. Entrena tu oído con acentos reales y velocidad nativa.</p>
                    </div>

                    <div class="bg-gray-800/50 p-8 rounded-2xl border border-gray-700 hover:border-yellow-500/50 transition duration-300">
                        <div class="w-12 h-12 bg-yellow-500/20 text-yellow-400 rounded-xl flex items-center justify-center mb-6">
                            <Subtitles size="24" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">2. Toca y Traduce</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">¿No entiendes una palabra? Solo tócala. Nuestra IA pausará el video y te dará la traducción exacta basada en el contexto de la escena.</p>
                    </div>

                    <div class="bg-gray-800/50 p-8 rounded-2xl border border-gray-700 hover:border-yellow-500/50 transition duration-300">
                        <div class="w-12 h-12 bg-purple-500/20 text-purple-400 rounded-xl flex items-center justify-center mb-6">
                            <Brain size="24" />
                        </div>
                        <h3 class="text-xl font-bold mb-3">3. Resuelve y Gana</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Demuestra lo que aprendiste respondiendo un Quiz generado por inteligencia artificial sobre el video. Gana puntos y sube de nivel.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>