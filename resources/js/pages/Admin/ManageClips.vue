<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3'; // 🌟 Agregamos useForm
import { 
    Search, Film, Trash2, Filter, Music, Monitor, Globe, Plus, Inbox, Edit2 // 🌟 Agregamos Edit2
} from 'lucide-vue-next';

const props = defineProps({
    clips: Array // Asumimos que el backend nos pasa todos los clips
});

// --- DICCIONARIO DE CATEGORÍAS ---
const categoryNames = {
    movies: 'Cine & TV',
    music: 'Música',
    tech: 'Tech & IA',
    travel: 'Viajes'
};

const getCategoryName = (slug) => categoryNames[slug] || slug;

const getCategoryIcon = (name) => {
    if (name === 'music') return Music;
    if (name === 'movies') return Film;
    if (name === 'tech') return Monitor;
    return Globe;
};

// --- FILTROS Y BÚSQUEDA ---
const searchQuery = ref('');
const selectedCategory = ref('all');

const filteredClips = computed(() => {
    if (!props.clips) return [];
    
    return props.clips.filter(clip => {
        // Filtro por búsqueda (título)
        const matchesSearch = clip.title.toLowerCase().includes(searchQuery.value.toLowerCase());
        // Filtro por categoría
        const matchesCategory = selectedCategory.value === 'all' || clip.category === selectedCategory.value;
        
        return matchesSearch && matchesCategory;
    });
});

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

// --- LÓGICA DE EDICIÓN ---
const clipToEdit = ref(null);

const editForm = useForm({
    title: '',
    category: ''
});

const openEditModal = (clip) => {
    clipToEdit.value = clip;
    editForm.title = clip.title;
    editForm.category = clip.category;
};

const closeEditModal = () => {
    clipToEdit.value = null;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('admin.clips.update', clipToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        }
    });
};
</script>

<template>
    <Head title="Gestionar Videos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-white leading-tight flex items-center gap-2">
                    <Film class="text-yellow-400" />
                    Biblioteca de Videos
                </h2>                
            </div>
        </template>

        <div class="py-12 relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 mb-8 flex flex-col sm:flex-row gap-4 shadow-lg">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" size="18" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar video por título..." 
                            class="w-full bg-gray-900 border border-gray-600 rounded-xl py-2.5 pl-10 pr-4 text-white focus:ring-yellow-500 focus:border-yellow-500 transition text-sm"
                        >
                    </div>
                    
                    <div class="relative w-full sm:w-64">
                        <Filter class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" size="18" />
                        <select 
                            v-model="selectedCategory"
                            class="w-full bg-gray-900 border border-gray-600 rounded-xl py-2.5 pl-10 pr-4 text-white focus:ring-yellow-500 focus:border-yellow-500 transition text-sm appearance-none"
                        >
                            <option value="all">Todas las categorías</option>
                            <option value="movies">Cine & TV</option>
                            <option value="music">Música</option>
                            <option value="tech">Tech & IA</option>
                            <option value="travel">Viajes</option>
                        </select>
                    </div>
                </div>

                <div class="bg-gray-800 border border-gray-700 rounded-3xl overflow-hidden shadow-2xl">
                    
                    <div v-if="filteredClips.length === 0" class="text-center py-16 px-4">
                        <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-700">
                            <Search class="text-gray-500" size="32" v-if="searchQuery !== '' || selectedCategory !== 'all'" />
                            <Inbox class="text-gray-500" size="32" v-else />
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">
                            {{ (searchQuery !== '' || selectedCategory !== 'all') ? 'No hay resultados' : 'Tu biblioteca está vacía' }}
                        </h3>
                        <p class="text-gray-400 text-sm">
                            {{ (searchQuery !== '' || selectedCategory !== 'all') ? 'Prueba buscando con otro término o cambiando de categoría.' : 'Sube tu primer video para empezar a poblar la plataforma.' }}
                        </p>
                    </div>

                    <div v-else class="divide-y divide-gray-700/50">
                        <div 
                            v-for="clip in filteredClips" 
                            :key="clip.id" 
                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-5 hover:bg-gray-750 transition duration-200 group relative"
                        >
                            <div class="w-24 h-14 bg-gray-900 rounded-lg flex items-center justify-center text-gray-500 border border-gray-700 shrink-0 overflow-hidden relative group-hover:border-yellow-500/50 transition">
                                <img v-if="clip.thumbnail_url" :src="clip.thumbnail_url" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition" />
                                <Film v-else size="24" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="text-white font-bold text-base truncate group-hover:text-yellow-400 transition" :title="clip.title">
                                    {{ clip.title }}
                                </h4>
                                <div class="flex flex-wrap items-center gap-3 mt-1.5">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-900 px-2 py-0.5 rounded-md border border-gray-700">
                                        <component :is="getCategoryIcon(clip.category)" size="12" class="text-yellow-500" />
                                        {{ getCategoryName(clip.category) }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-mono bg-black/30 px-2 py-0.5 rounded-md">ID: {{ clip.id }}</span>
                                    </div>
                            </div>

                            <div class="w-full sm:w-auto flex items-center justify-end gap-2 mt-4 sm:mt-0 pt-4 sm:pt-0 border-t border-gray-700 sm:border-none">
                                <Link 
                                    :href="route('flick.show', clip.id)" 
                                    class="px-3 py-1.5 text-sm font-medium text-gray-300 hover:text-white bg-gray-900 hover:bg-gray-700 rounded-lg transition border border-gray-700"
                                >
                                    Ver en App
                                </Link>

                                <button 
                                    @click="openEditModal(clip)"
                                    class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors border border-transparent hover:border-blue-500/20 focus:outline-none"
                                    title="Editar Video"
                                >
                                    <Edit2 size="18" />
                                </button>

                                <button 
                                    @click="confirmDelete(clip)"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors border border-transparent hover:border-red-500/20 focus:outline-none"
                                    title="Eliminar Video"
                                >
                                    <Trash2 size="18" />
                                </button>
                            </div>
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
            <div v-if="clipToEdit" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 max-w-md w-full shadow-2xl transform transition-all">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-700 pb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-500/10 flex items-center justify-center">
                            <Edit2 class="w-5 h-5 text-blue-500" />
                        </div>
                        <h3 class="text-xl font-bold text-white">Editar Información</h3>
                    </div>
                    
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Título del Video</label>
                            <input 
                                v-model="editForm.title" 
                                type="text" 
                                class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500 transition" 
                                required
                            >
                            <div v-if="editForm.errors.title" class="text-red-500 text-xs mt-1">{{ editForm.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Categoría</label>
                            <select 
                                v-model="editForm.category" 
                                class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500 transition appearance-none"
                                required
                            >
                                <option value="movies">Cine & TV 🎬</option>
                                <option value="music">Música 🎵</option>
                                <option value="tech">Tech & IA 💻</option>
                                <option value="travel">Viajes ✈️</option>
                            </select>
                            <div v-if="editForm.errors.category" class="text-red-500 text-xs mt-1">{{ editForm.errors.category }}</div>
                        </div>

                        <div class="flex gap-3 justify-end mt-8 pt-4 border-t border-gray-700">
                            <button 
                                type="button"
                                @click="closeEditModal" 
                                :disabled="editForm.processing"
                                class="px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 transition"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                :disabled="editForm.processing"
                                class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 min-w-[120px]"
                                :class="editForm.processing ? 'bg-blue-500/50 text-gray-300 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600 text-white shadow-[0_0_15px_rgba(59,130,246,0.3)]'"
                            >
                                <span v-if="editForm.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                {{ editForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

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
                        "<span class="font-bold text-white">{{ clipToDelete.title }}</span>" se borrará permanentemente. Esta acción no se puede deshacer.
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