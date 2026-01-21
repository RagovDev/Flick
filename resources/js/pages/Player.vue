<script setup>
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3'; // <--- 1. Importar usePage
import VideoPlayer from '@/components/VideoPlayer.vue';
import QuizOverlay from '@/components/QuizOverlay.vue';
import axios from 'axios';

const props = defineProps({
    initialClip: Object
});

// <--- 2. Inicializamos el puntaje con lo que tiene el usuario al entrar
const page = usePage();
const userScore = ref(page.props.auth.user.score); 

const currentClip = ref(props.initialClip);
const showQuiz = ref(false);
const isLoadingNext = ref(false);

const openQuiz = () => showQuiz.value = true;
const closeQuiz = () => showQuiz.value = false;

const handleAnswer = async (option) => {
    try {
        // Guardamos la respuesta en una variable para leer lo que devuelve el backend
        const response = await axios.post('/flick/check', {
            clip_id: currentClip.value.id,
            option_id: option.id
        });

        // <--- 3. EL TRUCO REACTIVO:
        // El backend nos devuelve 'total_score' actualizado. Lo asignamos a nuestra variable.
        // Vue detecta el cambio y actualiza el HUD instantáneamente.
        if (response.data.total_score) {
            userScore.value = response.data.total_score;
        }

        loadNextVideo();

    } catch (error) {
        console.error("Error al responder:", error);
        alert("Error guardando respuesta.");
    }
};

const loadNextVideo = async () => {
    isLoadingNext.value = true;
    showQuiz.value = false;

    try {
        const response = await axios.get('/flick/next');
        
        if (response.status === 204 || !response.data) {
            router.visit('/flick', { replace: true });
        } else {
            if (response.data.id === currentClip.value.id) {
                console.warn("⚠️ Mismo video recibido.");
            }
            currentClip.value = response.data;
        }
    } catch (error) {
        console.error("Error cargando siguiente:", error);
    } finally {
        isLoadingNext.value = false;
    }
};
</script>

<template>
    <Head title="Flick" />

    <div class="h-screen w-full bg-gray-900 flex justify-center overflow-hidden">
        <div class="w-full max-w-md h-full bg-black relative shadow-2xl overflow-hidden group">
            
            <VideoPlayer 
                :clip="currentClip" 
                :score="userScore"
                @open-quiz="openQuiz" 
            />

            <transition 
                enter-active-class="transition ease-out duration-300"
                enter-from-class="transform translate-y-full"
                enter-to-class="transform translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="transform translate-y-0"
                leave-to-class="transform translate-y-full"
            >
                <QuizOverlay 
                    v-if="showQuiz" 
                    :question="currentClip.questions[0]" 
                    @answered="handleAnswer"
                    @close="closeQuiz"
                />
            </transition>

            <div v-if="isLoadingNext" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center z-50 backdrop-blur-sm">
                <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-yellow-400 mb-4"></div>
                <p class="text-white font-bold animate-pulse">Cargando siguiente...</p>
            </div>

        </div>
    </div>
</template>