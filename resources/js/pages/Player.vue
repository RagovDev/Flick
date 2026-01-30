<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import VideoPlayer from '@/components/VideoPlayer.vue';
import QuizOverlay from '@/components/QuizOverlay.vue';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { ArrowLeft, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
    initialClip: Object,
    isPracticeMode: { type: Boolean, default: false }
});

// --- ESTADO ---
// Inicializamos el primer clip. Si ya viene resuelto del backend, idealmente debería tener una propiedad 'completed'.
// Por ahora, asumimos que si entra al player es para jugar.
const clips = ref([props.initialClip]); 
const currentIndex = ref(0);
const transitionName = ref('slide-up');
const isScrolling = ref(false);

const videoPlayerRef = ref(null);
const page = usePage();
const userScore = ref(page.props.auth.user.score);
const showQuiz = ref(false);
const isLoadingNext = ref(false);
const isShaking = ref(false);
const showSuccessToast = ref(false);

// Sonidos
const soundCorrect = new Audio('https://cdn.pixabay.com/audio/2021/08/04/audio_bb630cc098.mp3'); 
const soundWrong = new Audio('https://cdn.pixabay.com/download/audio/2022/03/24/audio_c8c8a73467.mp3?filename=wrong-answer-126515.mp3'); 
soundCorrect.volume = 0.5; soundWrong.volume = 0.6;


// --- NAVEGACIÓN ---

const goToNextVideo = async () => {
    if (isScrolling.value) return;
    isScrolling.value = true;
    transitionName.value = 'slide-up';

    if (currentIndex.value < clips.value.length - 1) {
        currentIndex.value++;
        showQuiz.value = false;
        setTimeout(() => isScrolling.value = false, 500);
        
        // ESTRATEGIA DE BUFFER:
        // Si estamos viendo el penúltimo video, cargamos uno más en el fondo
        if (currentIndex.value === clips.value.length - 1) {
            fetchNextClip();
        }
    } else {
        await fetchNextClip(); // Si llegamos al borde, forzamos carga
        if (currentIndex.value < clips.value.length - 1) {
             currentIndex.value++;
        }
    }
};

const goToPrevVideo = () => {
    if (isScrolling.value || currentIndex.value === 0) return;
    isScrolling.value = true;
    transitionName.value = 'slide-down';
    currentIndex.value--;
    showQuiz.value = false;
    setTimeout(() => isScrolling.value = false, 500);
};

const fetchNextClip = async () => {
    if (props.isPracticeMode) return;
    isLoadingNext.value = true;

    try {
        const response = await axios.get('/flick/next');
        if (response.status === 204 || !response.data) {
             // Fin del feed
        } else {
            // Verificamos que no esté duplicado por si acaso
            const exists = clips.value.some(c => c.id === response.data.id);
            if (!exists) {
                // Inicializamos la propiedad completed en false para el nuevo video
                const newClip = { ...response.data, completed: false };
                clips.value.push(newClip);
            }
        }
    } catch (error) {
        console.error("Error fetching next:", error);
    } finally {
        isLoadingNext.value = false;
        setTimeout(() => isScrolling.value = false, 500);
    }
};

// --- GESTOS ---
const handleWheel = (e) => {
    if (showQuiz.value) return;
    if (e.deltaY > 30) goToNextVideo();
    else if (e.deltaY < -30) goToPrevVideo();
};

let touchStartY = 0;
const handleTouchStart = (e) => { touchStartY = e.touches[0].clientY; };
const handleTouchEnd = (e) => {
    if (showQuiz.value) return;
    const diff = touchStartY - e.changedTouches[0].clientY;
    if (diff > 50) goToNextVideo();
    else if (diff < -50) goToPrevVideo();
};

// --- QUIZ & PUNTUACIÓN ---

const openQuiz = () => {
    // PROTECCIÓN: Si ya está completado, no abrimos el quiz (aunque el botón ya lo impide visualmente)
    if (clips.value[currentIndex.value].completed) return;
    showQuiz.value = true;
};

const closeQuiz = () => {
    showQuiz.value = false;
    if (videoPlayerRef.value) videoPlayerRef.value.resume();
};

const handleAnswer = async (option) => {
    // 1. MODO REPASO (Sin cambios)
    if (props.isPracticeMode) {
        if (option.is_correct) {
            soundCorrect.currentTime = 0; soundCorrect.play().catch(e => null);
            confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 }, colors: ['#60A5FA', '#ffffff'] });
            showSuccessToast.value = true;
            setTimeout(() => { router.visit('/dashboard'); }, 2000);
        } else {
            soundWrong.currentTime = 0; soundWrong.play().catch(e => null);
            triggerShake();
        }
        return; 
    }

    // 2. MODO JUEGO
    // Verificación de seguridad: Si ya se jugó este clip, no permitir reintentos
    if (clips.value[currentIndex.value].completed) return; 

    try {
        const response = await axios.post('/flick/check', {
            clip_id: clips.value[currentIndex.value].id,
            option_id: option.id
        });

        // *** CAMBIO CLAVE: "UNA SOLA VIDA" ***
        // Marcamos el clip como completado inmediatamente, sin importar el resultado.
        // Esto bloquea el botón para evitar farmear puntos o reintentar.
        clips.value[currentIndex.value].completed = true; 

        if (response.data.correct) {
            // --- ACIERTO (GANÓ) ---
            clips.value[currentIndex.value].won = true; // Flag para pintar el botón de VERDE

            soundCorrect.currentTime = 0; soundCorrect.play().catch(e => null);
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 }, colors: ['#FACC15', '#ffffff', '#00ff00'] });

            // Actualizar puntos
            if (response.data.total_score) userScore.value = response.data.total_score;

            // Avanzamos automáticamente
            setTimeout(() => { goToNextVideo(); }, 1500);
        } else {
            // --- ERROR (PERDIÓ) ---
            clips.value[currentIndex.value].won = false; // Flag para pintar el botón de ROJO

            soundWrong.currentTime = 0; soundWrong.play().catch(e => null);
            triggerShake();

            // Avanzamos automáticamente al siguiente video (Feed Infinito)
            // Cerramos el quiz y pasamos al siguiente
            setTimeout(() => { showQuiz.value = false; goToNextVideo(); }, 1000);
        }
    } catch (error) {
        console.error("Error check:", error);
    }
};

const triggerShake = () => { isShaking.value = true; setTimeout(() => isShaking.value = false, 500); };

// --- CICLO DE VIDA ---
onMounted(() => {
    window.addEventListener('wheel', handleWheel);
    window.addEventListener('touchstart', handleTouchStart);
    window.addEventListener('touchend', handleTouchEnd);
    
    // PRE-CARGA INTELIGENTE:
    // Cargamos el siguiente video inmediatamente para que el usuario no espere al deslizar
    fetchNextClip();
});

onBeforeUnmount(() => {
    window.removeEventListener('wheel', handleWheel);
    window.removeEventListener('touchstart', handleTouchStart);
    window.removeEventListener('touchend', handleTouchEnd);
});
</script>

<template>
    <Head title="Reproductor Flick" />

    <div class="h-screen w-full bg-gray-900 flex justify-center overflow-hidden relative">
        
        <Link :href="route('dashboard')" class="absolute top-4 left-4 z-50 flex items-center gap-2 bg-black/40 backdrop-blur-md px-4 py-2 rounded-full hover:bg-black/60 text-white transition border border-white/10 group">
            <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
            <span class="text-xs font-bold hidden sm:inline">Salir</span>
        </Link>

        <div v-if="isPracticeMode" class="absolute top-4 right-4 z-50 bg-blue-500/90 px-3 py-1 rounded-full text-xs font-black tracking-wider text-white shadow-lg border border-blue-400/50 animate-pulse">
            MODO REPASO
        </div>

        <div class="w-full max-w-md h-full bg-black relative shadow-2xl overflow-hidden" :class="{ 'shake-animation': isShaking }">
            
            <div class="relative w-full h-full overflow-hidden bg-black">
                <Transition :name="transitionName">
                    <VideoPlayer 
                        v-if="clips.length > 0"
                        :key="clips[currentIndex].id" 
                        ref="videoPlayerRef" 
                        :clip="clips[currentIndex]" 
                        :score="userScore"
                        class="absolute inset-0 w-full h-full" 
                        @open-quiz="openQuiz" 
                    />
                </Transition>
            </div>

            <transition enter-active-class="transition ease-out duration-300" enter-from-class="transform translate-y-full" enter-to-class="transform translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="transform translate-y-0" leave-to-class="transform translate-y-full">
                <QuizOverlay 
                    v-if="showQuiz && clips[currentIndex].questions && clips[currentIndex].questions[0]" 
                    :question="clips[currentIndex].questions[0]" 
                    @answered="handleAnswer"
                    @close="closeQuiz"
                />
            </transition>

            <div v-if="isLoadingNext" class="absolute bottom-10 left-1/2 -translate-x-1/2 z-50 bg-black/50 p-2 rounded-full backdrop-blur">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-white"></div>
            </div>

            <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showSuccessToast" class="absolute top-20 z-50 flex justify-center w-full px-4">
                    <div class="bg-gray-900/90 backdrop-blur-md border border-yellow-400/50 text-white px-6 py-4 rounded-2xl shadow-[0_0_30px_rgba(250,204,21,0.3)] flex items-center gap-4 max-w-sm w-full">
                        <div class="bg-yellow-400/20 p-2 rounded-full text-yellow-400 animate-bounce">
                            <CheckCircle size="32" stroke-width="2.5" />
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-yellow-400">¡Excelente Memoria!</h4>
                            <p class="text-sm text-gray-300">Volviendo al panel...</p>
                        </div>
                        <div class="absolute bottom-0 left-0 h-1 bg-yellow-400 animate-shrink w-full rounded-b-2xl"></div>
                    </div>
                </div>
            </transition>

        </div>
    </div>
</template>

<style scoped>
/* (MISMOS ESTILOS DE ANTES, NO HACE FALTA CAMBIARLOS) */
.shake-animation { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
.animate-shrink { animation: shrink 2s linear forwards; }
@keyframes shrink { from { width: 100%; } to { width: 0%; } }

.slide-up-enter-active, .slide-up-leave-active, .slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
  position: absolute; width: 100%; height: 100%;
}
.slide-up-enter-from { transform: translateY(100%); z-index: 10; }
.slide-up-enter-to { transform: translateY(0); z-index: 10; }
.slide-up-leave-from { transform: translateY(0); z-index: 0; filter: brightness(1); }
.slide-up-leave-to { transform: translateY(-20%); z-index: 0; filter: brightness(0.5); }

.slide-down-enter-from { transform: translateY(-100%); z-index: 10; }
.slide-down-enter-to { transform: translateY(0); z-index: 10; }
.slide-down-leave-from { transform: translateY(0); z-index: 0; filter: brightness(1); }
.slide-down-leave-to { transform: translateY(20%); z-index: 0; filter: brightness(0.5); }
</style>