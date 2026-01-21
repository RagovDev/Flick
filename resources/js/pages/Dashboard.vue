<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue'; // Apunta al archivo que acabamos de arreglar
import { Trophy, Video, Star, PlayCircle, Zap } from 'lucide-vue-next';

const props = defineProps({
    auth: Object,
    stats: Object,
    history: Array
});
</script>

<template>
    <Head title="Mi Progreso" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-xl text-gray-200 leading-tight">Centro de Mando</h2>
                <Link href="/flick" class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-2 px-4 rounded-full flex items-center gap-2 transition hover:scale-105">
                    <PlayCircle size="20" />
                    <span>Continuar Aprendiendo</span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700 p-8 relative">
                    <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                        <div class="relative">
                            <div class="absolute inset-0 bg-yellow-500 blur-2xl opacity-20 rounded-full"></div>
                            <Trophy class="text-yellow-400 w-24 h-24 relative z-10" />
                        </div>
                        
                        <div class="flex-1 w-full text-center md:text-left">
                            <h3 class="text-gray-400 text-sm uppercase tracking-widest font-bold">Nivel Actual</h3>
                            <div class="flex flex-col md:flex-row items-center md:items-end gap-2 md:gap-4 mb-2 justify-center md:justify-start">
                                <span class="text-5xl font-black text-white">{{ stats?.level || 1 }}</span>
                                <span class="text-yellow-500 font-bold text-xl mb-2">Novato del Inglés</span>
                            </div>
                            
                            <div class="w-full bg-gray-700 rounded-full h-4 mb-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 h-4 rounded-full transition-all duration-1000" :style="{ width: (stats?.progress_percent || 0) + '%' }"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400 font-mono">
                                <span>{{ auth.user.score }} XP Totales</span>
                                <span>Próximo Nivel: {{ stats?.next_level_points || 100 }} XP</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 flex items-center gap-4">
                        <div class="p-4 bg-blue-500/10 rounded-lg text-blue-400">
                            <Video size="32" />
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Videos Vistos</p>
                            <p class="text-2xl font-bold text-white">{{ stats?.total_watched || 0 }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 flex items-center gap-4">
                        <div class="p-4 bg-purple-500/10 rounded-lg text-purple-400">
                            <Star size="32" />
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Precisión</p>
                            <p class="text-2xl font-bold text-white">100%</p>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 flex items-center gap-4">
                        <div class="p-4 bg-green-500/10 rounded-lg text-green-400">
                            <Zap size="32" />
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Racha</p>
                            <p class="text-2xl font-bold text-white">1 Día</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Actividad Reciente</h3>
                        
                        <div v-if="!history || history.length === 0" class="text-gray-500 italic text-center py-4">
                            Aún no has visto videos. ¡Ve a aprender!
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="item in history" :key="item.id" class="flex items-center justify-between bg-gray-900/50 p-4 rounded-lg border border-gray-700/50 transition hover:border-gray-500">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 bg-gray-700 rounded flex items-center justify-center text-gray-500 text-xs font-bold">
                                        VIDEO
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">{{ item.title }}</h4>
                                        <span class="text-xs text-gray-500">Completado recientemente</span>
                                    </div>
                                </div>
                                <span 
                                    class="px-3 py-1 text-xs font-bold rounded-full"
                                    :class="item.score === 'Acertado' ? 'bg-green-500/20 text-green-400' : 'bg-gray-700 text-gray-400'"
                                >
                                    {{ item.score }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>