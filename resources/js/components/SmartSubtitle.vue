<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import { Check, Loader2, Volume2, X } from 'lucide-vue-next';

const props = defineProps({
    text: { type: String, required: true }
});

const emit = defineEmits(['word-clicked', 'popover-closed']); 

const loadingWordIndex = ref(null);
const savedWordIndex = ref(null);

const activeWordIndex = ref(null); 
const activeWordData = ref(null);  

const words = computed(() => {
    if (!props.text) return [];
    return props.text.split(' ');
});

const cleanWord = (word) => word.replace(/[.,/#!$%^&*;:{}=\-_`~()?"']/g, "").trim();

const handleWordClick = async (word, index) => {
    // 1. Si tocas la misma palabra, cerramos el toggle
    if (activeWordIndex.value === index) {
        activeWordIndex.value = null;
        emit('popover-closed'); 
        return;
    }

    emit('word-clicked'); // Pausa el video

    const term = cleanWord(word);
    if (!term) return;

    loadingWordIndex.value = index;
    activeWordIndex.value = null; // Cerramos otros popovers

    try {
        // PASO A: Traducir con la IA usando el contexto
        const translateResponse = await axios.post('/flick/translate', { 
            word: term,
            context: props.text 
        });
        
        // Ponemos un {} de salvavidas por si la IA falla completamente
        const translationData = translateResponse.data || {};

        // 🌟 1. MOSTRAMOS EL POPOVER AL INSTANTE (Súper rápido para el usuario)
        activeWordData.value = {
            term: term,
            // Si la IA no manda traducción, ponemos un mensaje claro en vez de "Cargando..."
            translation: translationData.translation || 'Traducción no disponible',
            phonetic: translationData.phonetic || '/.../'
        };
        
        activeWordIndex.value = index; // Abre el Popover
        loadingWordIndex.value = null; // Apaga el spinner de carga

        // 🌟 2. PASO B: Guardamos en tu base de datos EN SEGUNDO PLANO
        try {
            await axios.post(route('vocabulary.store'), {
                term: term,
                translation: activeWordData.value.translation,
                phonetic: activeWordData.value.phonetic === '/.../' ? '' : activeWordData.value.phonetic
            });
            
            // Feedback visual del check verde
            savedWordIndex.value = index;
            setTimeout(() => { savedWordIndex.value = null; }, 1500);
            
        } catch (saveError) {
            console.error("Error guardando en BD (pero la UI sigue funcionando):", saveError);
            // Si esto falla (ej. error 422), el usuario no se da cuenta porque ya está leyendo su traducción
        }

    } catch (error) {
        console.error("Error al contactar a Gemini:", error);
        
        // Si todo falla (ej. se va el internet), mostramos el error elegantemente
        activeWordData.value = {
            term: term,
            translation: 'Error de conexión',
            phonetic: '/.../'
        };
        activeWordIndex.value = index; 
        loadingWordIndex.value = null;
    }
};

const closePopover = () => {
    activeWordIndex.value = null;
    emit('popover-closed'); 
};
</script>

<template>
    <div class="text-center leading-relaxed select-none relative">
        
        <transition 
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div 
                v-if="activeWordIndex !== null && activeWordData" 
                class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-6 w-56 sm:w-64 z-50 pointer-events-auto cursor-default"
                @click.stop 
            >
                <div class="bg-gray-900/95 backdrop-blur-xl border border-gray-700 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] p-4 text-left relative overflow-hidden group-popover">
                    
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
                
            </div>
        </transition>

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

            <span v-if="loadingWordIndex === index" class="absolute -top-5 left-1/2 transform -translate-x-1/2">
                <Loader2 class="w-4 h-4 text-yellow-400 animate-spin drop-shadow-md" />
            </span>
            <span v-if="savedWordIndex === index && activeWordIndex !== index" class="absolute -top-5 left-1/2 transform -translate-x-1/2 animate-bounce-short">
                <Check class="w-5 h-5 text-green-400 font-black drop-shadow-md" stroke-width="3" />
            </span>
        </span>
    </div>
</template>