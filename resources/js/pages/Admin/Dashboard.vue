<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Film, Users, CheckCircle, 
    Music, Monitor, Globe, Plus, BarChart3, Inbox, Trash2 
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

const categoryNames = {
    movies: 'Cine & TV 🎬',
    music: 'Música 🎵',
    tech: 'Tech & IA 💻',
    travel: 'Viajes ✈️'
};

const getCategoryName = (slug) => categoryNames[slug] || slug;

// --- LÓGICA DE ELIMINACIÓN ---
const clipToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (clip) => {
    clipToDelete.value = clip;
};

const cancelDelete = () => {
    if (isDeleting.value) return;
    clipToDelete.value = null;
};

const executeDelete = () => {
    if (!clipToDelete.value || isDeleting.value) return;

    isDeleting.value = true;

    // Asume que tienes una ruta route('admin.clips.destroy', id) en tu backend
    router.delete(route('admin.clips.destroy', clipToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            clipToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-white leading-tight flex items-center gap-2">
                    <LayoutDashboard class="text-yellow-400" />
                    Panel de Control Flick
                </h2>
                
                <div class="flex gap-3 w-full sm:w-auto">
                    <Link :href="route('admin.clips.index')" class="flex-1 sm:flex-none bg-gray-800 border border-gray-600 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl flex items-center justify-center transition">
                        Ver Todos
                    </Link>
                    <Link :href="route('admin.clips.create')" class="flex-1 sm:flex-none bg-yellow-400 hover:bg-yellow-300 text-black font-black py-2.5 px-6 rounded-xl flex items-center justify-center gap-2 transition hover:scale-105 shadow-[0_0_15px_rgba(250,204,21,0.3)]">
                        <Plus size="20" stroke-width="3" />
                        <span class="hidden sm:inline">Subir Video</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl group hover:border-yellow-500/30 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Clips Totales</p>
                                <h3 class="text-4xl font-black text-white mt-2 group-hover:text-yellow-400 transition">{{ stats?.total_clips || 0 }}</h3>
                            </div>
                            <div class="p-3 bg-yellow-400/10 rounded-xl text-yellow-400 group-hover:scale-110 transition shadow-[0_0_10px_rgba(250,204,21,0.2)]">
                                <Film size="24" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl group hover:border-blue-500/30 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Estudiantes</p>
                                <h3 class="text-4xl font-black text-white mt-2 group-hover:text-blue-400 transition">{{ stats?.total_users || 0 }}</h3>
                            </div>
                            <div class="p-3 bg-blue-400/10 rounded-xl text-blue-400 group-hover:scale-110 transition shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                                <Users size="24" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl group hover:border-green-500/30 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Quizzes Resueltos</p>
                                <h3 class="text-4xl font-black text-white mt-2 group-hover:text-green-400 transition">{{ stats?.total_answers || 0 }}</h3>
                            </div>
                            <div class="p-3 bg-green-400/10 rounded-xl text-green-400 group-hover:scale-110 transition shadow-[0_0_10px_rgba(34,197,94,0.2)]">
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
                        
                        <div v-if="categories && categories.length > 0" class="space-y-6">
                            <div v-for="cat in categories" :key="cat.category" class="space-y-2 group">
                                <div class="flex justify-between text-sm font-bold">
                                    <span class="text-gray-300 flex items-center gap-2 group-hover:text-white transition">
                                        <component :is="getCategoryIcon(cat.category)" size="16" class="text-yellow-400" />
                                        {{ getCategoryName(cat.category) }}
                                    </span>
                                    <span class="text-white bg-gray-700 px-2 py-0.5 rounded-md text-xs">{{ cat.total }} videos</span>
                                </div>
                                <div class="w-full bg-gray-900 rounded-full h-3 overflow-hidden border border-gray-700">
                                    <div 
                                        class="bg-gradient-to-r from-yellow-600 to-yellow-400 h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(250,204,21,0.5)]"
                                        :style="{ width: ((cat.total / (stats.total_clips || 1)) * 100) + '%' }" 
                                    ></div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8">
                            <BarChart3 class="text-gray-600 w-12 h-12 mx-auto mb-3" />
                            <p class="text-gray-400 text-sm">Aún no hay videos categorizados.</p>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 shadow-xl">
                        <h4 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <Film class="text-yellow-400" size="20" />
                            Últimos Agregados
                        </h4>
                        
                        <div v-if="recentClips && recentClips.length > 0" class="space-y-3">
                            <div v-for="clip in recentClips" :key="clip.id" class="flex items-center gap-4 p-3 bg-gray-900/50 hover:bg-gray-700/50 rounded-xl border border-gray-700/50 transition duration-300">
                                <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center text-yellow-400 border border-gray-700 shrink-0">
                                    <Film size="20" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-white truncate" :title="clip.title">{{ clip.title }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mt-0.5 tracking-wider">{{ getCategoryName(clip.category) }}</p>
                                </div>
                                <div class="text-xs font-mono font-bold bg-black/50 text-gray-400 px-2 py-1 rounded-lg border border-gray-700">
                                    ID: {{ clip.id }}
                                </div>
                                
                                <button 
                                    @click="confirmDelete(clip)"
                                    class="p-2 ml-2 text-gray-500 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors focus:outline-none"
                                    title="Eliminar Video"
                                >
                                    <Trash2 size="18" />
                                </button>
                            </div>
                        </div>

                        <div v-else class="text-center py-12 bg-gray-900/50 rounded-xl border border-gray-700/50 border-dashed">
                            <Inbox class="text-gray-600 w-12 h-12 mx-auto mb-3" />
                            <p class="text-gray-400 text-sm mb-4">No has subido ningún video.</p>
                            <Link :href="route('admin.clips.create')" class="text-yellow-400 text-sm font-bold hover:underline">
                                Subir el primero &rarr;
                            </Link>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <transition 
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="clipToDelete" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 max-w-sm w-full shadow-2xl transform transition-all text-center">
                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                        <Trash2 class="w-6 h-6 text-red-500" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">¿Eliminar este video?</h3>
                    <p class="text-gray-400 text-sm mb-6">
                        "<span class="font-bold text-white">{{ clipToDelete.title }}</span>" se borrará permanentemente, junto con sus subtítulos y el quiz asociado.
                    </p>
                    <div class="flex gap-3 justify-center">
                        <button 
                            @click="cancelDelete" 
                            :disabled="isDeleting"
                            class="px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 disabled:opacity-50 transition"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="executeDelete" 
                            :disabled="isDeleting"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 min-w-[120px]"
                            :class="isDeleting ? 'bg-red-500/50 text-gray-300 cursor-not-allowed' : 'bg-red-500 hover:bg-red-600 text-white shadow-[0_0_15px_rgba(239,68,68,0.3)]'"
                        >
                            <span v-if="isDeleting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            {{ isDeleting ? 'Borrando...' : 'Sí, eliminar' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

    </AuthenticatedLayout>
</template>