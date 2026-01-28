<script setup>
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import VideoPlayer from '@/components/VideoPlayer.vue';
import QuizOverlay from '@/components/QuizOverlay.vue';
import axios from 'axios';
// 1. Importamos la librería de fiesta
import confetti from 'canvas-confetti';

const props = defineProps({
    initialClip: Object
});

const page = usePage();
const userScore = ref(page.props.auth.user.score); 
const currentClip = ref(props.initialClip);
const showQuiz = ref(false);
const isLoadingNext = ref(false);

// Variable para el efecto de temblor
const isShaking = ref(false);

// 2. Pre-cargamos los sonidos (Usando URLs públicas para prueba rápida)
const soundCorrect = new Audio('/audio/quiz/soundCorrect.mp3'); // Ding suave
const soundWrong = new Audio('/audio/quiz/soundWrong.mp3'); // Error sutil
soundCorrect.volume = 0.5;
soundWrong.volume = 0.4;

const openQuiz = () => showQuiz.value = true;
const closeQuiz = () => showQuiz.value = false;

const handleAnswer = async (option) => {
    try {
        const response = await axios.post('/flick/check', {
            clip_id: currentClip.value.id,
            option_id: option.id
        });

        if (response.data.correct) {
            // --- CASO: ACIERTO ---
            
            // A. Sonido de victoria
            soundCorrect.currentTime = 0;
            soundCorrect.play().catch(e => console.log('Audio bloqueado por navegador', e));

            // B. Explosión de Confeti
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#FACC15', '#ffffff', '#00ff00'] // Amarillo, Blanco, Verde
            });

            // C. Actualizar puntos
            if (response.data.total_score) {
                userScore.value = response.data.total_score;
            }

            // D. Esperar un momento para celebrar antes de cambiar
            setTimeout(() => {
                loadNextVideo();
            }, 1500); // 1.5 segundos de gloria

        } else {
            // --- CASO: ERROR ---
            
            // A. Sonido de error
            soundWrong.currentTime = 0;
            soundWrong.play().catch(e => console.log('Audio bloqueado', e));

            // B. Efecto Shake (Temblor)
            triggerShake();

            // C. (Opcional) Cerrar el quiz o dejarlo para que intente de nuevo
            // Por ahora cerramos para no frustrar, pero no cargamos el siguiente video inmediatamente
            // o dejamos que el usuario lo cierre manualmente.
            // Para mantener el flujo rápido:
            setTimeout(() => {
               showQuiz.value = false;
               loadNextVideo();
            }, 1000);
        }

    } catch (error) {
        console.error("Error al responder:", error);
    }
};

const triggerShake = () => {
    isShaking.value = true;
    setTimeout(() => isShaking.value = false, 500); // El temblor dura 0.5s
};

const loadNextVideo = async () => {
    isLoadingNext.value = true;
    showQuiz.value = false;

    try {
        const response = await axios.get('/flick/next');
        
        if (response.status === 204 || !response.data) {
            // router.visit('/dashboard', { replace: true }); // Descomenta esto cuando quieras redirigir al final
             alert("¡Has visto todos los videos disponibles! Vuelve pronto.");
             router.visit('/dashboard');
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
        
        <div 
            class="w-full max-w-md h-full bg-black relative shadow-2xl overflow-hidden group"
            :class="{ 'shake-animation': isShaking }"
        >
            
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

<style scoped>
/* Animación de Temblor (Shake) */
.shake-animation {
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>