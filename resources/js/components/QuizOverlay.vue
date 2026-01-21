<script setup>
import { ref } from 'vue';
import { X } from 'lucide-vue-next'; // Asegúrate de que esta línea esté aquí

const props = defineProps({
    question: Object,
});

const emit = defineEmits(['answered', 'close']); 

const selectedOptionId = ref(null);
const isSubmitting = ref(false);

const handleSelect = (option) => {
    if (isSubmitting.value) return;
    
    selectedOptionId.value = option.id;
    isSubmitting.value = true;

    setTimeout(() => {
        emit('answered', option);
        selectedOptionId.value = null;
        isSubmitting.value = false;
    }, 500);
};
</script>

<template>
    <div class="absolute bottom-0 left-0 w-full bg-gray-900/95 backdrop-blur-xl border-t border-white/10 rounded-t-3xl p-6 z-40 transition-transform duration-300 shadow-2xl">
        
        <button 
            @click="$emit('close')" 
            class="absolute top-4 right-4 p-2 text-gray-400 hover:text-white bg-white/5 rounded-full hover:bg-white/20 transition z-50 cursor-pointer"
        >
            <X :size="24" />
        </button>

        <div class="w-12 h-1.5 bg-gray-600 rounded-full mx-auto mb-8"></div>

        <h3 class="text-xl text-white font-bold text-center mb-8 leading-tight px-2">
            {{ question.statement }}
        </h3>

        <div class="space-y-3">
            <button 
                v-for="option in question.options" 
                :key="option.id"
                @click="handleSelect(option)"
                class="w-full p-4 rounded-xl text-left font-medium transition-all duration-200 border-2 active:scale-95"
                :class="[
                    selectedOptionId === option.id 
                        ? 'bg-yellow-400 border-yellow-400 text-black shadow-[0_0_15px_rgba(234,179,8,0.4)]' 
                        : 'bg-white/5 border-white/10 text-white hover:bg-white/10 hover:border-white/30'
                ]"
            >
                {{ option.text }}
            </button>
        </div>
    </div>
</template>