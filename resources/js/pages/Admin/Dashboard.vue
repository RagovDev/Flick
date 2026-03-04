<script setup>
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Film, Users, CheckCircle, 
    Music, Monitor, Globe, Plus, BarChart3 
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps({
    stats: Object,
    categories: Array,
    recentClips: Array
});

// Helper para iconos de categoría
const getCategoryIcon = (name) => {
    if (name === 'music') return Music;
    if (name === 'movies') return Film;
    if (name === 'tech') return Monitor;
    return Globe;
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-white leading-tight flex items-center gap-2">
                    <LayoutDashboard class="text-yellow-400" />
                    Panel de Control Flick
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Clips Totales</p>
                                <h3 class="text-4xl font-black text-white mt-2">{{ stats.total_clips }}</h3>
                            </div>
                            <div class="p-3 bg-yellow-400/10 rounded-xl text-yellow-400">
                                <Film size="24" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Estudiantes</p>
                                <h3 class="text-4xl font-black text-white mt-2">{{ stats.total_users }}</h3>
                            </div>
                            <div class="p-3 bg-blue-400/10 rounded-xl text-blue-400">
                                <Users size="24" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Quizzes Resueltos</p>
                                <h3 class="text-4xl font-black text-white mt-2">{{ stats.total_answers }}</h3>
                            </div>
                            <div class="p-3 bg-green-400/10 rounded-xl text-green-400">
                                <CheckCircle size="24" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 shadow-xl">
                        <div class="flex items-center gap-2 mb-8">
                            <BarChart3 class="text-yellow-400" size="20" />
                            <h4 class="text-xl font-bold text-white">Contenido por Canal</h4>
                        </div>
                        
                        <div class="space-y-6">
                            <div v-for="cat in categories" :key="cat.category" class="space-y-2">
                                <div class="flex justify-between text-sm font-bold">
                                    <span class="text-gray-300 uppercase flex items-center gap-2">
                                        <component :is="getCategoryIcon(cat.category)" size="14" class="text-yellow-400" />
                                        {{ cat.category }}
                                    </span>
                                    <span class="text-white">{{ cat.total }} videos</span>
                                </div>
                                <div class="w-full bg-gray-900 rounded-full h-3 overflow-hidden">
                                    <div 
                                        class="bg-yellow-400 h-full rounded-full transition-all duration-1000"
                                        :style="{ width: (cat.total / stats.total_clips * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 shadow-xl">
                        <h4 class="text-xl font-bold text-white mb-6">Últimos Agregados</h4>
                        <div class="space-y-4">
                            <div v-for="clip in recentClips" :key="clip.id" class="flex items-center gap-4 p-3 bg-gray-900/50 rounded-xl border border-gray-700/50">
                                <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center text-yellow-400 border border-gray-700">
                                    <Film size="20" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ clip.title }}</p>
                                    <p class="text-xs text-gray-500 uppercase">{{ clip.category }}</p>
                                </div>
                                <div class="text-xs font-mono text-gray-600">
                                    ID: {{ clip.id }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>