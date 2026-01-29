<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { Search, BookOpen, Brain, Star, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    words: Array
});

const search = ref('');

// Filtrar palabras en tiempo real
const filteredWords = computed(() => {
    return props.words.filter(word => 
        word.term.toLowerCase().includes(search.value.toLowerCase()) ||
        word.translation.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Función para determinar el color según el nivel de maestría (Gamificación)
const getLevelColor = (level) => {
    if (level === 0) return 'bg-gray-600'; // Nuevo
    if (level < 3) return 'bg-yellow-500'; // Aprendiendo
    return 'bg-green-500'; // Maestro
};
</script>

<template>
    <Head title="Mi Vocabulario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-200 leading-tight">Mi Colección</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div class="flex gap-4">
                        <div class="bg-gray-800 px-4 py-2 rounded-lg border border-gray-700 flex items-center gap-3">
                            <BookOpen class="text-blue-400" size="20" />
                            <div>
                                <span class="block text-xl font-bold text-white">{{ words.length }}</span>
                                <span class="text-xs text-gray-400">Palabras</span>
                            </div>
                        </div>
                        <div class="bg-gray-800 px-4 py-2 rounded-lg border border-gray-700 flex items-center gap-3">
                            <Brain class="text-yellow-400" size="20" />
                            <div>
                                <span class="block text-xl font-bold text-white">0</span>
                                <span class="text-xs text-gray-400">Repasos Pendientes</span>
                            </div>
                        </div>
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
                    <Link href="/flick" class="mt-4 inline-block text-yellow-400 hover:underline font-bold">
                        Ir a Aprender &rarr;
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div 
                        v-for="word in filteredWords" 
                        :key="word.id"
                        class="bg-gray-800 p-5 rounded-xl border border-gray-700 hover:border-yellow-500/50 transition group relative overflow-hidden"
                    >
                        <div class="absolute left-0 top-0 bottom-0 w-1" :class="getLevelColor(word.level)"></div>

                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white capitalize">{{ word.term }}</h3>
                            <button class="text-gray-600 hover:text-red-400 transition">
                                <Trash2 size="16" />
                            </button>
                        </div>

                        <p class="text-gray-400 text-sm mb-4 italic">{{ word.translation || 'Sin traducción' }}</p>

                        <div class="flex items-center justify-between text-xs text-gray-500 mt-4 border-t border-gray-700 pt-3">
                            <span class="flex items-center gap-1">
                                <Star size="12" :class="word.level > 0 ? 'text-yellow-500' : 'text-gray-600'" />
                                Nvl {{ word.level }}
                            </span>
                            <span>{{ word.added_at }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>