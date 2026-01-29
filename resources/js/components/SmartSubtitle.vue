<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import { Check, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    text: { type: String, required: true }
});

// 1. DEFINIMOS EL EMIT
const emit = defineEmits(['word-clicked']);

const loadingWordIndex = ref(null);
const savedWordIndex = ref(null);

const words = computed(() => {
    if (!props.text) return [];
    return props.text.split(' ');
});

const cleanWord = (word) => word.replace(/[.,/#!$%^&*;:{}=\-_`~()?"']/g, "").trim();

const handleWordClick = async (word, index) => {
    // 2. AVISAMOS INMEDIATAMENTE PARA QUE PAUSE EL VIDEO
    emit('word-clicked');

    const term = cleanWord(word);
    if (!term) return;

    loadingWordIndex.value = index;

    try {
        await axios.post('/vocabulary/save', { term: term });
        savedWordIndex.value = index;
        setTimeout(() => { savedWordIndex.value = null; }, 2000);
    } catch (error) {
        console.error(error);
    } finally {
        loadingWordIndex.value = null;
    }
};
</script>

<template>
    <div class="text-center leading-snug select-none">
        <span 
            v-for="(word, index) in words" 
            :key="index"
            class="
                inline-block mx-0.5 my-0.5 cursor-pointer transition-all duration-200 relative
                text-white text-lg md:text-2xl font-medium tracking-normal
                drop-shadow-[0_1.5px_1.5px_rgba(0,0,0,0.9)]
                hover:text-yellow-400 hover:scale-105 hover:-translate-y-0.5 hover:drop-shadow-[0_0_8px_rgba(250,204,21,0.8)]
            "
            @click.stop="handleWordClick(word, index)"
        >
            {{ word }}
            
            <span v-if="loadingWordIndex === index" class="absolute -top-5 left-1/2 -translate-x-1/2">
                <Loader2 class="w-4 h-4 text-yellow-400 animate-spin drop-shadow-md" />
            </span>

            <span v-if="savedWordIndex === index" class="absolute -top-5 left-1/2 -translate-x-1/2 animate-bounce-short">
                <Check class="w-5 h-5 text-green-400 font-black drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]" stroke-width="3" />
            </span>
        </span>
    </div>
</template>