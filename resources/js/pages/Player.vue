<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import VideoPlayer from '@/components/VideoPlayer.vue';
import QuizOverlay from '@/components/QuizOverlay.vue';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { ArrowLeft, CheckCircle, RotateCcw } from 'lucide-vue-next';

const props = defineProps({
    initialClip: Object,
    isPracticeMode: { type: Boolean, default: false },
    activeCategory: { type: String, default: null } 
});

const clips = ref([{
    ...props.initialClip,
    queue_id: Date.now() + Math.random() 
}]); 
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

const soundCorrect = new Audio('/audio/quiz/correct.mp3'); 
const soundWrong = new Audio('/audio/quiz/wrong.mp3'); 
soundCorrect.volume = 0.5; soundWrong.volume = 0.6;

const showStreakAnimation = ref(false); 
const user = computed(() => page.props.auth.user); 
const xpGained = ref(10); 
const isCriticalHit = ref(false); 
const isStreakSaved = ref(false); 
const currentStreak = ref(0); // 🌟 NUEVO: Para nutrir la animación al instante

const maintainBuffer = async () => {
    if (props.isPracticeMode) return;
    while ((clips.value.length - 1 - currentIndex.value) < 2) {
        if (isLoadingNext.value) break; 
        await fetchNextClip();
    }
};

const fetchNextClip = async () => {
    isLoadingNext.value = true;
    try {
        const response = await axios.get('/flick/next', {
            params: { category: props.activeCategory } 
        });
        if (response.status !== 204 && response.data) {
            const newClip = {
                ...response.data,
                queue_id: Date.now() + Math.random()
            };
            clips.value.push(newClip);
        }
    } catch (error) {
        console.error("Error fetching next:", error);
    } finally {
        isLoadingNext.value = false;
    }
};

const goToNextVideo = () => {
    if (isScrolling.value) return;
    if (currentIndex.value < clips.value.length - 1) {
        isScrolling.value = true;
        transitionName.value = 'slide-up';
        currentIndex.value++;
        showQuiz.value = false;
        
        setTimeout(() => isScrolling.value = false, 500);
        maintainBuffer();
    } else {
        isLoadingNext.value = true;
        maintainBuffer().then(() => {
            if (currentIndex.value < clips.value.length - 1) {
                goToNextVideo(); 
            }
        });
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

const openQuiz = () => {
    if (clips.value[currentIndex.value].completed) return;
    showQuiz.value = true;
};

const closeQuiz = () => {
    showQuiz.value = false;
    if (videoPlayerRef.value) videoPlayerRef.value.resume();
};

const handleAnswer = async (option) => {
    if (clips.value[currentIndex.value].completed) return; 

    try {
        const response = await axios.post('/flick/check', {
            clip_id: clips.value[currentIndex.value].id,
            option_id: option.id
        });

        clips.value[currentIndex.value].completed = true; 

        if (response.data.correct) {
            clips.value[currentIndex.value].won = true; 
            soundCorrect.currentTime = 0; soundCorrect.play().catch(e => null);
            
            if (props.isPracticeMode) {
                confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 }, colors: ['#60A5FA', '#ffffff'] });
            } else {
                confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 }, colors: ['#FACC15', '#ffffff', '#00ff00'] });
            }
            
            // 🌟 Atrapamos TODOS los datos al instante
            if (response.data.total_score) userScore.value = response.data.total_score;
            xpGained.value = response.data.points_earned;
            isCriticalHit.value = response.data.is_critical;
            isStreakSaved.value = response.data.streak_saved; 
            currentStreak.value = response.data.streak; // 🌟 Sin latencia
            
            router.reload({ only: ['auth'] }); 

            if (isCriticalHit.value || isStreakSaved.value) {
                showStreakAnimation.value = true;
                
                setTimeout(() => {
                    showStreakAnimation.value = false;
                    if (props.isPracticeMode) {
                        router.visit('/dashboard');
                    } else {
                        goToNextVideo(); 
                    }
                }, 2500); 

            } else {
                if (props.isPracticeMode) {
                    setTimeout(() => router.visit('/dashboard'), 1500);
                } else {
                    setTimeout(() => goToNextVideo(), 1000); 
                }
            }

        } else {
            clips.value[currentIndex.value].won = false;
            soundWrong.currentTime = 0; soundWrong.play().catch(e => null);
            triggerShake();
            
            if (!props.isPracticeMode) {
                setTimeout(() => { showQuiz.value = false; goToNextVideo(); }, 1000);
            }
        }
    } catch (error) {
        console.error("Error check:", error);
    }
};

const triggerShake = () => { isShaking.value = true; setTimeout(() => isShaking.value = false, 500); };

onMounted(() => {
    window.addEventListener('wheel', handleWheel);
    window.addEventListener('touchstart', handleTouchStart);
    window.addEventListener('touchend', handleTouchEnd);
    maintainBuffer();
});

onBeforeUnmount(() => {
    window.removeEventListener('wheel', handleWheel);
    window.removeEventListener('touchstart', handleTouchStart);
    window.removeEventListener('touchend', handleTouchEnd);
});
</script>

<template>
    <Head title="Reproductor Flick" />

    <div class="h-screen w-full bg-gray-900 flex flex-col items-center justify-center overflow-hidden relative">
        
        <div class="hidden">
            <video v-if="clips[currentIndex + 1]" :src="clips[currentIndex + 1].video_url" preload="auto"></video>
            <video v-if="clips[currentIndex + 2]" :src="clips[currentIndex + 2].video_url" preload="auto"></video>
        </div>

        <Link :href="route('dashboard')" class="absolute top-4 left-4 z-50 flex items-center justify-center sm:justify-start gap-2 bg-black/40 backdrop-blur-md w-10 h-10 sm:w-auto sm:h-auto sm:px-4 sm:py-2 rounded-full hover:bg-black/60 text-white transition border border-white/10 group">
            <ArrowLeft class="w-5 h-5 sm:w-4 sm:h-4 group-hover:-translate-x-1 transition-transform" />
            <span class="text-xs font-bold hidden sm:inline">Salir</span>
        </Link>

        <div v-if="isPracticeMode" class="absolute top-4 right-4 z-50 bg-blue-500/90 px-3 py-1 rounded-full text-xs font-black tracking-wider text-white shadow-lg border border-blue-400/50 animate-pulse">
            MODO REPASO
        </div>

        <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-90" enter-to-class="opacity-100 scale-100">
            <div v-if="!isPracticeMode && clips.length > 0 && clips[currentIndex]?.completed" 
                 class="absolute top-4 right-16 z-50 flex items-center gap-1.5 bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-bold text-gray-300 border border-gray-500/50 shadow-lg">
                <RotateCcw class="w-3.5 h-3.5" />
                <span>REPASO</span>
            </div>
        </transition>

        <div class="w-full h-full sm:h-[85vh] sm:w-auto sm:aspect-[9/16] bg-black relative shadow-2xl overflow-hidden sm:rounded-3xl sm:border sm:border-gray-800 transition-all duration-300 flex-shrink-0" :class="{ 'shake-animation': isShaking }">
            
            <div class="relative w-full h-full overflow-hidden bg-black">
                <Transition :name="transitionName">
                    <VideoPlayer 
                        v-if="clips.length > 0"
                        :key="clips[currentIndex].queue_id"  ref="videoPlayerRef" 
                        :clip="clips[currentIndex]" 
                        :score="userScore"
                        class="absolute inset-0 w-full h-full object-cover" 
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

        </div>
    </div>

    <transition
        enter-active-class="transition ease-out duration-500"
        enter-from-class="opacity-0 scale-50 translate-y-20"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition ease-in duration-300"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-75"
    >
        <div v-if="showStreakAnimation" class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none bg-black/70 backdrop-blur-md">
            
            <div class="flex flex-col items-center justify-center text-center" :class="{'animate-pulse': isCriticalHit}">
            
            <div class="text-9xl mb-4" :class="isCriticalHit ? 'animate-spin-slow drop-shadow-[0_0_50px_rgba(168,85,247,0.8)]' : 'animate-bounce drop-shadow-[0_0_50px_rgba(249,115,22,0.8)]'">
                <template v-if="isCriticalHit && isStreakSaved">🌟🔥</template>
                <template v-else-if="isCriticalHit">🌟</template>
                <template v-else>🔥</template>
            </div>
            
            <h2 class="text-5xl font-black tracking-widest uppercase drop-shadow-2xl" 
                :class="isCriticalHit ? 'text-purple-400' : 'text-white'">
                <template v-if="isCriticalHit && isStreakSaved">¡Crítico y Racha!</template>
                <template v-else-if="isCriticalHit">¡Golpe Crítico!</template>
                <template v-else>¡Racha Salvada!</template>
            </h2>
            
            <p class="text-3xl font-bold mt-3 drop-shadow-md flex items-center gap-2"
            :class="isCriticalHit ? 'text-purple-300' : 'text-orange-400'">
                {{ currentStreak }} {{ currentStreak === 1 ? 'Día Seguido' : 'Días Seguidos' }}
            </p>
            
            <div class="mt-6 px-8 py-2 border-2 rounded-full font-black text-2xl tracking-widest transition-all scale-110"
                :class="isCriticalHit ? 'bg-purple-500/30 border-purple-400 text-purple-300 shadow-[0_0_25px_rgba(168,85,247,0.6)]' : 'bg-yellow-400/20 border-yellow-400 text-yellow-400 shadow-[0_0_15px_rgba(250,204,21,0.4)]'">
                +{{ xpGained }} XP
            </div>
            
        </div>
        </div>
    </transition>
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