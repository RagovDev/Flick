<script setup>
import { ref } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import VideoPlayer from '@/components/VideoPlayer.vue';
import QuizOverlay from '@/components/QuizOverlay.vue';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { ArrowLeft, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
    initialClip: Object,
    isPracticeMode: {
        type: Boolean,
        default: false
    }
});

// Referencia al componente VideoPlayer (Hijo)
const videoPlayerRef = ref(null);

const page = usePage();
const userScore = ref(page.props.auth.user.score);
const currentClip = ref(props.initialClip);
const showQuiz = ref(false);
const isLoadingNext = ref(false);
const isShaking = ref(false);
const showSuccessToast = ref(false); // Para el toast de modo práctica

// Sonidos
const soundCorrect = new Audio('https://cdn.pixabay.com/audio/2021/08/04/audio_bb630cc098.mp3'); 
const soundWrong = new Audio('https://cdn.pixabay.com/download/audio/2022/03/24/audio_c8c8a73467.mp3?filename=wrong-answer-126515.mp3'); 
soundCorrect.volume = 0.5;
soundWrong.volume = 0.6;

const openQuiz = () => showQuiz.value = true;

// NUEVO: Cerrar quiz y reanudar video automáticamente
const closeQuiz = () => {
    showQuiz.value = false;
    // Si la referencia existe, llamamos al método resume() que expusimos
    if (videoPlayerRef.value) {
        videoPlayerRef.value.resume();
    }
};

const handleAnswer = async (option) => {
    // --- LÓGICA DE MODO REPASO ---
    if (props.isPracticeMode) {
        if (option.is_correct) {
            soundCorrect.currentTime = 0;
            soundCorrect.play().catch(e => null);
            confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 }, colors: ['#60A5FA', '#ffffff'] });
            
            showSuccessToast.value = true;
            setTimeout(() => { router.visit('/dashboard'); }, 2000);
        } else {
            soundWrong.currentTime = 0;
            soundWrong.play().catch(e => null);
            triggerShake();
        }
        return; 
    }

    // --- LÓGICA DE MODO JUEGO NORMAL ---
    try {
        const response = await axios.post('/flick/check', {
            clip_id: currentClip.value.id,
            option_id: option.id
        });

        if (response.data.correct) {
            soundCorrect.currentTime = 0;
            soundCorrect.play().catch(e => null);
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 }, colors: ['#FACC15', '#ffffff', '#00ff00'] });

            if (response.data.total_score) userScore.value = response.data.total_score;
            setTimeout(() => { loadNextVideo(); }, 1500);
        } else {
            soundWrong.currentTime = 0;
            soundWrong.play().catch(e => null);
            triggerShake();
            setTimeout(() => { showQuiz.value = false; loadNextVideo(); }, 1000);
        }
    } catch (error) {
        console.error("Error check:", error);
    }
};

const triggerShake = () => {
    isShaking.value = true;
    setTimeout(() => isShaking.value = false, 500);
};

const loadNextVideo = async () => {
    if (props.isPracticeMode) return;
    isLoadingNext.value = true;
    showQuiz.value = false;

    try {
        const response = await axios.get('/flick/next');
        if (response.status === 204 || !response.data) {
             alert("¡Has visto todos los videos!");
             router.visit('/dashboard');
        } else {
            currentClip.value = response.data;
        }
    } catch (error) {
        console.error("Error next:", error);
    } finally {
        isLoadingNext.value = false;
    }
};
</script>

<template>
    <Head title="Reproductor Flick" />

    <div class="h-screen w-full bg-gray-900 flex justify-center overflow-hidden relative">
        
        <Link href="/dashboard" class="absolute top-4 left-4 z-50 flex items-center gap-2 bg-black/40 backdrop-blur-md px-4 py-2 rounded-full hover:bg-black/60 text-white transition border border-white/10 group">
            <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
            <span class="text-xs font-bold hidden sm:inline">Salir</span>
        </Link>

        <div v-if="isPracticeMode" class="absolute top-4 right-4 z-50 bg-blue-500/90 px-3 py-1 rounded-full text-xs font-black tracking-wider text-white shadow-lg border border-blue-400/50 animate-pulse">
            MODO REPASO
        </div>

        <div class="w-full max-w-md h-full bg-black relative shadow-2xl overflow-hidden" :class="{ 'shake-animation': isShaking }">
            
            <VideoPlayer 
                ref="videoPlayerRef" 
                :clip="currentClip" 
                :score="userScore"
                @open-quiz="openQuiz" 
            />

            <transition enter-active-class="transition ease-out duration-300" enter-from-class="transform translate-y-full" enter-to-class="transform translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="transform translate-y-0" leave-to-class="transform translate-y-full">
                <QuizOverlay 
                    v-if="showQuiz" 
                    :question="currentClip.questions[0]" 
                    @answered="handleAnswer"
                    @close="closeQuiz"
                />
            </transition>

            <div v-if="isLoadingNext" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center z-50 backdrop-blur-sm">
                <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-yellow-400 mb-4 shadow-[0_0_15px_rgba(250,204,21,0.5)]"></div>
                <p class="text-white font-bold animate-pulse">Cargando siguiente reto...</p>
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
.shake-animation { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
.animate-shrink { animation: shrink 2s linear forwards; }
@keyframes shrink { from { width: 100%; } to { width: 0%; } }
</style>