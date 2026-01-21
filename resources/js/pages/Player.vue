<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3'; // Importamos 'router' para poder recargar
import VideoPlayer from '@/components/VideoPlayer.vue';
import QuizOverlay from '@/components/QuizOverlay.vue';
import axios from 'axios';

const props = defineProps({
    initialClip: Object
});

const currentClip = ref(props.initialClip);
const showQuiz = ref(false);
const isLoadingNext = ref(false);

// Acciones del Quiz
const openQuiz = () => showQuiz.value = true;
const closeQuiz = () => showQuiz.value = false;

// Manejar Respuesta
const handleAnswer = async (option) => {
    try {
        // 1. Enviamos la respuesta al backend (sin importar si es correcta o no)
        await axios.post('/flick/check', {
            clip_id: currentClip.value.id,
            option_id: option.id
        });

        // 2. Avanzamos inmediatamente al siguiente video
        loadNextVideo();

    } catch (error) {
        console.error("Error al responder:", error);
        alert("Error guardando respuesta. Revisa la consola.");
    }
};

// Cargar Siguiente Video (Lógica de Infinite Scroll)
const loadNextVideo = async () => {
    isLoadingNext.value = true;
    showQuiz.value = false; // Aseguramos que el quiz se cierre

    try {
        const response = await axios.get('/flick/next');
        
        // Si el status es 204 (No Content) o viene vacío, es que se acabaron los videos
        if (response.status === 204 || !response.data) {
            
            // LA MAGIA: Recargamos la página ('/flick').
            // Al recargar, Laravel ejecutará el controlador 'index()', verá que 
            // no quedan videos pendientes y cargará el componente 'Completed.vue'.
            router.visit('/flick', {
                replace: true, // Reemplaza el historial para que no puedan volver atrás
            });

        } else {
            // Verificación de seguridad por si el servidor devuelve el mismo
            if (response.data.id === currentClip.value.id) {
                console.warn("⚠️ El servidor devolvió el mismo video.");
            }
            
            // Reemplazamos el clip actual. Al cambiar esta variable,
            // el componente VideoPlayer se reinicia gracias al :key="clip.video_url"
            currentClip.value = response.data;
        }
    } catch (error) {
        console.error("Error cargando siguiente video:", error);
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