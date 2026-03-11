<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { Search, BookOpen, Brain, Star, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Toast from '@/components/Toast.vue'; 

const props = defineProps({
    words: Array
});

const search = ref('');

// --- LÓGICA DEL TOAST ---
const showToast = ref(false);
const toastMessage = ref('');

const triggerToast = (msg) => {
    toastMessage.value = msg;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// --- LÓGICA DEL MODAL DE ELIMINACIÓN ---
const wordToDelete = ref(null);

const confirmDelete = (word) => {
    wordToDelete.value = word; // Abre el modal con la palabra seleccionada
};

const cancelDelete = () => {
    wordToDelete.value = null; // Cierra el modal
};

const executeDelete = () => {
    if (!wordToDelete.value) return;

    router.delete(route('vocabulary.destroy', wordToDelete.value.id), {
        preserveScroll: true, // Evita que la página salte
        onSuccess: () => {
            wordToDelete.value = null; // Cerramos el modal
            triggerToast('Palabra eliminada con éxito'); // Mostramos el Toast
        }
    });
};

// --- FILTROS Y ESTILOS ---
const filteredWords = computed(() => {
    return props.words.filter(word => 
        word.term.toLowerCase().includes(search.value.toLowerCase()) ||
        word.translation.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Función para determinar el color según el nivel de maestría
const getLevelColor = (level) => {
    if (level === 0) return 'bg-gray-600 shadow-[0_0_10px_rgba(75,85,99,0.5)]'; 
    if (level < 3) return 'bg-yellow-500 shadow-[0_0_10px_rgba(234,179,8,0.4)]'; 
    return 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.4)]'; 
};
</script>

<template>
    <Head title="Mi Vocabulario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-200 leading-tight">Mi Colección</h2>
        </template>

        <div class="py-12 relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="bg-gray-800 px-4 py-2 rounded-lg border border-gray-700 flex items-center gap-3">
                            <BookOpen class="text-blue-400" size="20" />
                            <div>
                                <span class="block text-xl font-bold text-white">{{ words.length }}</span>
                                <span class="text-xs text-gray-400">Palabras</span>
                            </div>
                        </div>
                        
                        <Link 
                            :href="route('vocabulary.practice')"
                            class="bg-yellow-400 hover:bg-yellow-500 text-black px-5 py-2 rounded-lg font-bold flex items-center gap-2 shadow-lg shadow-yellow-400/20 transition hover:scale-105"
                            v-if="words.length > 0"
                        >
                            <Brain size="20" />
                            <span>Practicar Ahora</span>
                        </Link>
                    </div>

                    <div class="relative w-full md:w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" size="18" />
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Buscar palabra..." 
                            class="w-full bg-gray-900 border border-gray-700 rounded-full py-2 pl-10 pr-4 text-white focus:ring-yellow-400 focus:border-yellow-400 text-sm"
                        >
                    </div>
                </div>

                <div v-if="filteredWords.length === 0" class="text-center py-20 bg-gray-800 rounded-2xl border border-dashed border-gray-700">
                    <div class="bg-gray-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Search class="text-gray-400" size="32" />
                    </div>
                    <h3 class="text-white font-bold text-lg">No encontramos palabras</h3>
                    <p class="text-gray-400 text-sm mt-2">Ve a ver videos y toca los subtítulos para guardar palabras.</p>
                    <Link :href="route('dashboard')" class="mt-4 inline-block text-yellow-400 hover:underline font-bold">
                        Ir a Aprender &rarr;
                    </Link>
                </div>

                <div v-else> 
                    <transition-group 
                        tag="div" 
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        leave-active-class="transition duration-300 ease-in absolute" 
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div 
                            v-for="word in filteredWords" 
                            :key="word.id"
                            class="bg-gray-800 p-5 rounded-xl border border-gray-700 hover:border-yellow-500/50 transition group relative overflow-hidden"
                        >
                            <div class="absolute left-0 top-0 bottom-0 w-1" :class="getLevelColor(word.level)"></div>

                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <h3 class="text-xl font-bold text-white capitalize">{{ word.term }}</h3>
                                    <p v-if="word.phonetic" class="text-gray-400 text-[11px] font-mono tracking-widest mt-0.5">
                                        {{ word.phonetic }} 🔊
                                    </p>
                                </div>
                                <button 
                                    @click="confirmDelete(word)" 
                                    class="text-gray-500 hover:text-red-500 transition-colors p-1"
                                    title="Eliminar palabra"
                                >
                                    <Trash2 class="w-4 h-4" /> 
                                </button>
                            </div>

                            <p class="text-yellow-400 font-medium text-sm mt-2 mb-4">{{ word.translation || 'Sin traducción' }}</p>

                            <div class="flex items-center justify-between text-xs text-gray-500 mt-4 border-t border-gray-700 pt-3">
                                <span class="flex items-center gap-1">
                                    <Star size="12" :class="word.level > 0 ? 'text-yellow-500' : 'text-gray-600'" />
                                    Nvl {{ word.level }}
                                </span>
                                <span>{{ word.added_at }}</span>
                            </div>
                        </div>
                    </transition-group>
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
            <div v-if="wordToDelete" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 max-w-sm w-full shadow-2xl transform transition-all text-center">
                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                        <Trash2 class="w-6 h-6 text-red-500" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">¿Eliminar palabra?</h3>
                    <p class="text-gray-400 text-sm mb-6">
                        "<span class="font-bold text-white">{{ wordToDelete.term }}</span>" desaparecerá de tu colección.
                    </p>
                    <div class="flex gap-3 justify-center">
                        <button @click="cancelDelete" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 transition">
                            Cancelar
                        </button>
                        <button @click="executeDelete" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-900 bg-yellow-400 hover:bg-yellow-500 transition shadow-[0_0_15px_rgba(250,204,21,0.3)]">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <Toast :show="showToast" :message="toastMessage" />

    </AuthenticatedLayout>
</template>