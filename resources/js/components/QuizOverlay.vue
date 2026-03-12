<script setup>
import { ref, onBeforeUnmount, computed } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
    question: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['answered', 'close']); 

const selectedOptionId = ref(null);
const isRevealed = ref(false);
const isSubmitting = ref(false);
let timeoutId = null; // 🌟 Guardamos la referencia al temporizador

// 🌟 MAGIA PURA: Creamos una copia del arreglo y lo desordenamos aleatoriamente
const shuffledOptions = computed(() => {
    if (!props.question || !props.question.options) return [];
    
    // El operador [...] crea una copia para no mutar la prop original
    // sort(() => Math.random() - 0.5) es el truco clásico para mezclar arreglos
    return [...props.question.options].sort(() => Math.random() - 0.5);
});

const handleSelect = (option) => {
    if (isRevealed.value) return;
    if (isSubmitting.value) return;
    
    selectedOptionId.value = option.id;
    isSubmitting.value = true;
    isRevealed.value = true;

    // Guardamos el ID del timeout
    timeoutId = setTimeout(() => {
        emit('answered', option);
        selectedOptionId.value = null;
        isSubmitting.value = false;
    }, 500);
};

// 🌟 Función segura para cerrar
const handleClose = () => {
    if (isSubmitting.value) return; // Bloquea la "X" si ya está enviando respuesta
    emit('close');
};

// 🌟 Limpieza de memoria (Best Practice)
// Si el componente desaparece de repente, matamos el temporizador fantasma
onBeforeUnmount(() => {
    if (timeoutId) clearTimeout(timeoutId);
});
</script>

<template>
    <div 
        v-if="question && question.options"
        role="dialog" 
        aria-modal="true"
        class="absolute bottom-0 left-0 w-full bg-gray-900/95 backdrop-blur-xl border-t border-gray-700/50 rounded-t-3xl p-6 z-40 transition-transform duration-300 shadow-2xl"
    >
        
        <button 
            @click="handleClose" 
            :disabled="isSubmitting"
            class="absolute top-4 right-4 p-2 text-gray-400 hover:text-white bg-white/5 rounded-full hover:bg-white/20 transition z-50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            title="Cerrar pregunta"
        >
            <X :size="24" />
        </button>

        <div class="w-12 h-1.5 bg-gray-700 rounded-full mx-auto mb-8"></div>

        <h3 class="text-xl text-white font-bold text-center mb-8 leading-tight px-2">
            {{ question.statement }}
        </h3>

        <div class="space-y-3">
            <button 
                v-for="option in shuffledOptions" 
                :key="option.id"
                @click="handleSelect(option)"
                :disabled="isRevealed"
                class="w-full p-4 rounded-xl text-left font-medium transition-all duration-300 border-2 disabled:cursor-default"
                :class="[
                    // Estado Normal (Antes de hacer clic)
                    !isRevealed ? 'bg-white/5 border-white/10 text-white hover:bg-white/10 hover:border-white/30 active:scale-95' : '',
                    
                    // Si ya se reveló y esta es la opción CORRECTA (Se pinta verde sí o sí)
                    isRevealed && option.is_correct ? 'bg-green-500/20 border-green-500 text-green-400 shadow-[0_0_15px_rgba(34,197,94,0.3)] scale-105' : '',
                    
                    // Si ya se reveló, el usuario la seleccionó, pero es INCORRECTA (Se pinta roja)
                    isRevealed && selectedOptionId === option.id && !option.is_correct ? 'bg-red-500/20 border-red-500 text-red-400 shadow-[0_0_15px_rgba(239,68,68,0.3)] shake-animation' : '',
                    
                    // Si ya se reveló y son las incorrectas que el usuario NO tocó (Se apagan)
                    isRevealed && selectedOptionId !== option.id && !option.is_correct ? 'bg-transparent border-transparent text-gray-600 opacity-50 scale-95' : ''
                ]"
            >
                {{ option.text }}
            </button>
        </div>
    </div>
</template>