<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import { Check, Loader2, Volume2, X } from 'lucide-vue-next';

const props = defineProps({
    text: { type: String, required: true }
});

const emit = defineEmits(['word-clicked', 'popover-closed']); // Agregamos 'popover-closed'

const loadingWordIndex = ref(null);
const savedWordIndex = ref(null);

// NUEVO: Estado para el Popover
const activeWordIndex = ref(null); // Qué palabra tiene el popover abierto
const activeWordData = ref(null);  // Datos de la palabra (traducción, etc)

const words = computed(() => {
    if (!props.text) return [];
    return props.text.split(' ');
});

const cleanWord = (word) => word.replace(/[.,/#!$%^&*;:{}=\-_`~()?"']/g, "").trim();

const handleWordClick = async (word, index) => {
    // 1. Si tocas la misma palabra, cerramos el toggle
    if (activeWordIndex.value === index) {
        activeWordIndex.value = null;
        emit('popover-closed'); // <--- AVISAMOS AQUÍ
        return;
    }

    emit('word-clicked'); // Pausar video (ya existente)

    const term = cleanWord(word);
    if (!term) return;

    loadingWordIndex.value = index;
    activeWordIndex.value = null; // Cerramos otros popovers mientras carga

    try {
        const response = await axios.post('/vocabulary/save', { term: term });
        
        // 2. Guardamos datos para mostrar en el Popover
        activeWordData.value = response.data.word;
        
        // Feedback visual
        savedWordIndex.value = index;
        activeWordIndex.value = index; // ABRIMOS EL POPOVER

        // Quitamos el check verde rápido, pero dejamos el popover abierto
        setTimeout(() => { savedWordIndex.value = null; }, 1500);

    } catch (error) {
        console.error(error);
    } finally {
        loadingWordIndex.value = null;
    }
};

const closePopover = () => {
    activeWordIndex.value = null;
    emit('popover-closed'); // <--- AVISAMOS AQUÍ TAMBIÉN (Botón X)
};
</script>

<template>
    <div class="text-center leading-snug select-none relative">
        <span 
            v-for="(word, index) in words" 
            :key="index"
            class="
                inline-block mx-0.5 my-0.5 cursor-pointer transition-all duration-200 relative
                text-white text-lg md:text-2xl font-medium tracking-normal
                drop-shadow-[0_1.5px_1.5px_rgba(0,0,0,0.9)]
                hover:text-yellow-400 hover:scale-105
            "
            :class="{ 'text-yellow-400 scale-105': activeWordIndex === index }"
            @click.stop="handleWordClick(word, index)"
        >
            {{ word }}

            <span v-if="loadingWordIndex === index" class="absolute -top-5 left-1/2 -translate-x-1/2">
                <Loader2 class="w-4 h-4 text-yellow-400 animate-spin drop-shadow-md" />
            </span>

            <span v-if="savedWordIndex === index && activeWordIndex !== index" class="absolute -top-5 left-1/2 -translate-x-1/2 animate-bounce-short">
                <Check class="w-5 h-5 text-green-400 font-black drop-shadow-md" stroke-width="3" />
            </span>

            <transition 
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translate-y-2 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-2 scale-95"
            >
                <div 
                    v-if="activeWordIndex === index && activeWordData" 
                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-48 z-50 pointer-events-auto cursor-default"
                    @click.stop 
                >
                    <div class="bg-gray-900/95 backdrop-blur-xl border border-gray-700 rounded-xl shadow-2xl p-4 text-left relative overflow-hidden group-popover">
                        
                        <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-500/10 rounded-full blur-xl -mr-8 -mt-8"></div>

                        <button @click.stop="closePopover" class="absolute top-2 right-2 text-gray-500 hover:text-white transition">
                            <X size="14" />
                        </button>

                        <div class="mb-2">
                            <h3 class="text-white font-bold text-lg capitalize leading-none">{{ activeWordData.term }}</h3>
                            <div class="flex items-center gap-1 text-gray-400 text-xs mt-1 font-mono">
                                <span>{{ activeWordData.phonetic || '/.../' }}</span>
                                <Volume2 size="10" class="text-gray-500" />
                            </div>
                        </div>

                        <div class="border-t border-gray-700/50 pt-2 mt-2">
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-0.5">Significado</p>
                            <p class="text-yellow-400 font-bold text-base leading-tight">
                                {{ activeWordData.translation || 'Cargando...' }}
                            </p>
                        </div>
                        
                    </div>

                    <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-gray-900 border-b border-r border-gray-700 transform rotate-45"></div>
                </div>
            </transition>
        </span>
    </div>
</template>