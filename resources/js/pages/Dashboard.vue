<script setup>
import { computed } from 'vue'; // 🌟 Agregamos computed
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue'; 
import { Trophy, Video, Star, PlayCircle, Zap, Play, CheckCircle, XCircle, Clock, Film, Music, Cpu, Globe, Rocket } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps({
    auth: Object,
    stats: Object,
    history: Array
});

// 🌟 UX PRO: Extraemos solo el primer nombre para un saludo más amigable y que no rompa el diseño
const firstName = computed(() => {
    if (!props.auth.user.name) return '';
    return props.auth.user.name.split(' ')[0];
});

const getStatusColor = (score) => {
    if (score === 'Acertado') return 'bg-green-500/10 text-green-400 border-green-500/20';
    if (score === 'Fallado') return 'bg-red-500/10 text-red-400 border-red-500/20';
    return 'bg-gray-700/30 text-gray-400 border-gray-600/30';
};

const getStatusIcon = (score) => {
    if (score === 'Acertado') return CheckCircle;
    if (score === 'Fallado') return XCircle;
    return Clock;
};

const channels = [
    { id: 'movies', name: 'Cine & TV', icon: Film, color: 'from-red-500 to-orange-500', desc: 'Frases icónicas' },
    { id: 'music', name: 'Música', icon: Music, color: 'from-pink-500 to-rose-500', desc: 'Letras y ritmo' },
    { id: 'tech', name: 'Tech & IA', icon: Cpu, color: 'from-blue-500 to-cyan-500', desc: 'Innovación' },
    { id: 'travel', name: 'Viajes', icon: Globe, color: 'from-green-500 to-emerald-500', desc: 'Cultura global' },
];
</script>

<template>
    <Head title="Mi Progreso" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-white leading-tight flex items-center gap-2">
                        Centro de Mando
                        <span class="text-xs bg-yellow-500 text-black px-2 py-0.5 rounded font-bold uppercase tracking-wider">BETA</span>
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Bienvenido de nuevo, {{ firstName }}</p>
                </div>
                
                <Link :href="route('flick.index')" class="w-full sm:w-auto bg-yellow-400 hover:bg-yellow-300 text-black font-black py-3 px-6 rounded-xl flex items-center justify-center gap-2 transition hover:scale-105 shadow-[0_0_20px_rgba(250,204,21,0.3)] group">
                    <PlayCircle size="22" class="group-hover:rotate-12 transition-transform" />
                    <span>MIX ALEATORIO</span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-gray-800/50 backdrop-blur-sm overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-700 p-8 relative group">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

                    <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                        <div class="relative group-hover:scale-110 transition duration-500">
                            <div class="absolute inset-0 bg-yellow-500 blur-2xl opacity-20 rounded-full group-hover:opacity-40 transition duration-700 animate-pulse"></div>
                            <Trophy class="text-yellow-400 w-24 h-24 relative z-10 drop-shadow-[0_0_15px_rgba(250,204,21,0.6)]" stroke-width="1.5" />
                        </div>
                        
                        <div class="flex-1 w-full text-center md:text-left">
                            <h3 class="text-gray-500 text-xs uppercase tracking-[0.2em] font-bold mb-1">Nivel Actual</h3>
                            
                            <div class="flex flex-col md:flex-row items-center md:items-end gap-3 mb-4 justify-center md:justify-start">
                                <span class="text-6xl font-black text-white leading-none tracking-tight">{{ stats?.level || 1 }}</span>
                                <div class="text-left">
                                    <span class="block text-yellow-400 font-bold text-xl leading-none">Novato del Inglés</span>
                                    <span class="text-gray-400 text-xs">Sigue así para subir a Aprendiz</span>
                                </div>
                            </div>
                            
                            <div class="relative w-full h-3 bg-gray-700/50 rounded-full mb-2 overflow-hidden border border-gray-600/50">
                                <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(250,204,21,0.6)] relative" :style="{ width: (stats?.progress_percent || 0) + '%' }">
                                    <div class="absolute top-0 right-0 bottom-0 w-1 bg-white/50"></div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-xs font-mono font-medium">
                                <span class="text-yellow-400/80">{{ auth.user.score }} XP <span class="text-gray-500">Acumulados</span></span>
                                <span class="text-gray-400">Próximo Nivel: <span class="text-white">{{ stats?.next_level_points || 100 }} XP</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-800/50 p-6 rounded-2xl border border-gray-700/50 flex items-center gap-4 hover:bg-gray-800 hover:border-blue-500/30 transition duration-300 group">
                        <div class="p-3 bg-blue-500/10 rounded-xl text-blue-400 group-hover:scale-110 transition shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                            <Video size="28" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Videos Vistos</p>
                            <p class="text-2xl font-black text-white">{{ stats?.total_watched || 0 }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-800/50 p-6 rounded-2xl border border-gray-700/50 flex items-center gap-4 hover:bg-gray-800 hover:border-purple-500/30 transition duration-300 group relative overflow-hidden opacity-70">
                        <div class="absolute -right-8 top-3 bg-yellow-500 text-black text-[9px] font-black uppercase tracking-widest py-0.5 px-8 rotate-45 shadow-lg">Pronto</div>
                        <div class="p-3 bg-purple-500/10 rounded-xl text-purple-400 group-hover:scale-110 transition shadow-[0_0_10px_rgba(168,85,247,0.2)]">
                            <Star size="28" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Precisión</p>
                            <p class="text-2xl font-black text-white">100%</p>
                        </div>
                    </div>

                    <div class="bg-gray-800/50 p-6 rounded-2xl border border-gray-700/50 flex items-center gap-4 hover:bg-gray-800 hover:border-green-500/30 transition duration-300 group relative overflow-hidden opacity-70">
                        <div class="absolute -right-8 top-3 bg-yellow-500 text-black text-[9px] font-black uppercase tracking-widest py-0.5 px-8 rotate-45 shadow-lg">Pronto</div>
                        <div class="p-3 bg-green-500/10 rounded-xl text-green-400 group-hover:scale-110 transition shadow-[0_0_10px_rgba(34,197,94,0.2)]">
                            <Zap size="28" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Racha Actual</p>
                            <p class="text-2xl font-black text-white flex items-center gap-1">
                                1 <span class="text-sm font-medium text-gray-400 mt-1">Día</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <Rocket size="20" class="text-yellow-400" />
                        Explora por Canales
                    </h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link 
                            v-for="channel in channels" 
                            :key="channel.id"
                            :href="route('flick.index', { category: channel.id })" 
                            class="group relative overflow-hidden rounded-2xl h-32 cursor-pointer transition-all hover:scale-[1.02] hover:shadow-xl"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br opacity-80 group-hover:opacity-100 transition duration-300" :class="channel.color"></div>
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition"></div>

                            <div class="absolute inset-0 p-4 flex flex-col justify-between">
                                <div class="bg-white/20 w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-sm self-start group-hover:scale-110 transition">
                                    <component :is="channel.icon" class="text-white" size="20" />
                                </div>
                                
                                <div class="relative z-10 translate-y-0 group-hover:-translate-y-1 transition duration-300">
                                    <h4 class="text-white font-black text-lg leading-none tracking-tight">{{ channel.name }}</h4>
                                    <p class="text-white/80 text-xs font-medium mt-1">{{ channel.desc }}</p>
                                </div>
                            </div>

                            <component :is="channel.icon" class="absolute -bottom-4 -right-4 text-white/20 rotate-12 group-hover:rotate-0 group-hover:scale-110 transition duration-500" size="80" />
                        </Link>
                    </div>
                </div>

                <div class="bg-gray-800/30 overflow-hidden sm:rounded-2xl border border-gray-700/50">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <Clock size="20" class="text-gray-400" />
                            Actividad Reciente
                        </h3>
                        
                        <div v-if="!history || history.length === 0" class="text-gray-500 italic text-center py-12 bg-gray-900/20 rounded-xl border border-dashed border-gray-700/50">
                            <p>Tu historial está vacío.</p>
                            <Link :href="route('flick.index')" class="text-yellow-400 font-bold hover:underline mt-2 inline-block">
                                ¡Empieza tu racha hoy!
                            </Link>
                        </div>

                        <div v-else class="space-y-3">
                            <Link 
                                v-for="item in history" 
                                :key="item.id" 
                                :href="route('flick.show', item.clip_id)" 
                                class="group flex items-center justify-between bg-gray-900/40 p-3 rounded-xl border border-gray-700/30 transition-all duration-300 hover:border-yellow-500/40 hover:bg-gray-800 hover:translate-x-1 relative overflow-hidden"
                            >
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="h-16 w-28 bg-black rounded-lg overflow-hidden border border-gray-700 group-hover:border-yellow-400/50 transition-colors shadow-lg relative shrink-0">
                                        <img 
                                            v-if="item.thumbnail_url" 
                                            :src="item.thumbnail_url" 
                                            alt="Thumbnail" 
                                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500 opacity-80 group-hover:opacity-100"
                                        />
                                        <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center">
                                            <Video class="text-gray-600" />
                                        </div>
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                            <PlayCircle class="text-white drop-shadow-md" size="24" />
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-bold group-hover:text-yellow-400 transition-colors line-clamp-1">{{ item.title }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-400 group-hover:text-gray-300 transition-colors">Ver de nuevo</span>
                                            <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                                            <span class="text-xs text-gray-500">{{ item.date_human }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative z-10 pl-4">
                                    <span 
                                        class="px-3 py-1.5 text-xs font-bold rounded-lg border flex items-center gap-1.5 min-w-[100px] justify-center shadow-sm"
                                        :class="getStatusColor(item.score)"
                                    >
                                        <component :is="getStatusIcon(item.score)" size="14" stroke-width="2.5" />
                                        {{ item.score }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>