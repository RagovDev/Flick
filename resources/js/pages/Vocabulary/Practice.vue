<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, RotateCw, Check, X, Trophy } from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';

const props = defineProps({
    words: Array
});

// Estado del juego
const currentIndex = ref(0);
const isFlipped = ref(false);
const completed = ref(false);
const score = ref(0);

// Palabra actual
const currentWord = computed(() => props.words[currentIndex.value]);

// Progreso (Barra superior)
const progress = computed(() => {
    if (props.words.length === 0) return 0;
    // 🌟 MEJORA UX: Le sumamos 1 para que la barra no empiece vacía en la primera tarjeta
    return ((currentIndex.value + 1) / props.words.length) * 100;
});

// Acciones
const flipCard = () => {
    isFlipped.value = !isFlipped.value;
};

const handleResult = (success) => {
    if (success) score.value++;
    
    // 🌟 1. GIRAR INMEDIATAMENTE: Ocultamos las respuestas y empezamos el giro a la inversa
    isFlipped.value = false;

    // 🌟 2. CAMBIAR EN SECRETO: Esperamos 250ms a que la tarjeta esté "de canto" 
    // (girada a 90 grados, invisible) para cambiar el texto sin que el usuario se dé cuenta.
    setTimeout(() => {
        if (currentIndex.value < props.words.length - 1) {
            currentIndex.value++;
        } else {
            completed.value = true;
        }
    }, 250); // Exactamente la mitad de la duración de tu animación (500ms)
};
</script>

<template>
    <Head title="Entrenamiento" />

    <div class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-4 relative overflow-hidden">
        
        <Link :href="route('vocabulary.index')" class="absolute top-6 left-6 flex items-center gap-2 text-gray-400 hover:text-white transition z-20">
            <ArrowLeft /> <span>Salir</span>
        </Link>

        <div v-if="completed" class="text-center z-10 animate-fade-in-up">
            <div class="bg-yellow-400/10 p-6 rounded-full inline-block mb-6 border border-yellow-400/20">
                <Trophy class="w-16 h-16 text-yellow-400" />
            </div>
            <h1 class="text-4xl font-bold mb-2">¡Sesión Completada!</h1>
            <p class="text-gray-400 mb-8 text-lg">Recordaste {{ score }} de {{ words.length }} palabras.</p>
            
            <div class="flex gap-4 justify-center">
                <Link :href="route('dashboard')" class="px-6 py-3 bg-gray-700 rounded-xl hover:bg-gray-600 transition font-bold">
                    Volver al Dashboard
                </Link>
                <Link :href="route('vocabulary.practice')" class="px-6 py-3 bg-yellow-400 text-black rounded-xl hover:bg-yellow-300 transition font-bold">
                    Practicar Otra Vez
                </Link>
            </div>
        </div>

        <div v-else-if="words.length > 0" class="w-full max-w-md flex flex-col gap-8 z-10">
            
            <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full bg-yellow-400 transition-all duration-500" :style="{ width: progress + '%' }"></div>
            </div>

            <p class="text-center text-gray-400 font-mono text-sm">TARJETA {{ currentIndex + 1 }} / {{ words.length }}</p>

            <div class="perspective-1000 h-80 w-full cursor-pointer group" @click="flipCard">
                <div 
                    class="relative w-full h-full text-center transition-transform duration-500 transform-style-3d"
                    :class="{ 'rotate-y-180': isFlipped }"
                >
                    <div class="absolute inset-0 backface-hidden bg-gray-800 border-2 border-gray-700 rounded-3xl flex flex-col items-center justify-center shadow-2xl group-hover:border-yellow-400/50 transition-colors">
                        <span class="text-xs text-yellow-500 font-bold tracking-widest uppercase mb-4">INGLÉS</span>
                        <h2 class="text-4xl font-black text-white">{{ currentWord.term }}</h2>
                        <p class="mt-8 text-gray-500 text-sm flex items-center gap-2">
                            <RotateCw size="14" /> Toca para ver significado
                        </p>
                    </div>

                    <div class="absolute inset-0 backface-hidden bg-white text-black border-2 border-white rounded-3xl flex flex-col items-center justify-center shadow-2xl rotate-y-180">
                        <span class="text-xs text-gray-500 font-bold tracking-widest uppercase mb-4">SIGNIFICADO</span>
                        <h2 class="text-3xl font-bold text-gray-900 px-4">{{ currentWord.translation }}</h2>
                    </div>
                </div>
            </div>

            <div 
                class="flex justify-between gap-4 transition-all duration-300"
                :class="isFlipped ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 pointer-events-none'"
            >
                <button 
                    @click="handleResult(false)"
                    class="flex-1 py-4 bg-red-500/10 border border-red-500/50 text-red-500 rounded-xl font-bold hover:bg-red-500 hover:text-white transition flex items-center justify-center gap-2"
                >
                    <X /> No la sabía
                </button>
                <button 
                    @click="handleResult(true)"
                    class="flex-1 py-4 bg-green-500/10 border border-green-500/50 text-green-500 rounded-xl font-bold hover:bg-green-500 hover:text-white transition flex items-center justify-center gap-2"
                >
                    <Check /> ¡Fácil!
                </button>
            </div>

        </div>

        <div v-else class="text-center">
            <h2 class="text-2xl font-bold mb-2">Sin palabras aún</h2>
            <p class="text-gray-400 mb-6">Necesitas guardar palabras viendo videos para poder practicar.</p>
            <Link :href="route('flick.index')" class="bg-yellow-400 text-black px-6 py-2 rounded-full font-bold hover:bg-yellow-300">
                Ir a Ver Videos
            </Link>
        </div>

    </div>
</template>

<style scoped>
/* Clases utilitarias para el efecto 3D flip */
.perspective-1000 {
    perspective: 1000px;
}
.transform-style-3d {
    transform-style: preserve-3d;
}
.backface-hidden {
    backface-visibility: hidden;
}
.rotate-y-180 {
    transform: rotateY(180deg);
}
</style>