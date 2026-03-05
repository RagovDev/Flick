<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Heart } from 'lucide-vue-next';

const props = defineProps({
    clipId: Number,
    initialIsLiked: Boolean,
    initialCount: Number
});

// 1. Declaramos que este componente puede emitir un evento
const emit = defineEmits(['like-toggled']);

const isLiked = ref(props.initialIsLiked);
const count = ref(props.initialCount || 0);
const isAnimating = ref(false);

const toggleLike = async () => {
    if (isAnimating.value) return;
    
    isAnimating.value = true;
    setTimeout(() => isAnimating.value = false, 500);

    const prevLiked = isLiked.value;
    const prevCount = count.value;

    // Cambio visual optimista
    isLiked.value = !isLiked.value;
    count.value = isLiked.value ? count.value + 1 : count.value - 1;

    // 2. AVISAMOS AL PADRE DEL CAMBIO INMEDIATAMENTE
    emit('like-toggled', { isLiked: isLiked.value, count: count.value });

    try {
        const response = await axios.post(route('flick.like', props.clipId));
        isLiked.value = response.data.status === 'liked';
        
        // Avisamos de nuevo por si el servidor hizo alguna corrección
        emit('like-toggled', { isLiked: isLiked.value, count: count.value });
    } catch (error) {
        // Si hay error, revertimos y avisamos
        isLiked.value = prevLiked;
        count.value = prevCount;
        emit('like-toggled', { isLiked: isLiked.value, count: count.value });
        console.error("Error en el Like:", error);
    }
};
</script>

<template>
    <div class="flex flex-col items-center gap-1">
        <button 
            @click.stop="toggleLike" 
            class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md transition-all duration-300 shadow-lg active:scale-75 group"
            :class="{'bg-red-500/20 border-red-500/50': isLiked}"
        >
            <Heart 
                :size="24" 
                stroke-width="2.5" 
                class="transition-all duration-300"
                :class="[
                    isLiked ? 'text-red-500 fill-red-500 scale-110' : 'text-white group-hover:text-red-400',
                    {'animate-ping': isAnimating && isLiked}
                ]"
            />
        </button>
        <span class="text-[10px] font-black text-white drop-shadow-md uppercase tracking-tighter">
            {{ count > 0 ? count : 'Like' }}
        </span>
    </div>
</template>