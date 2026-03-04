<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3'; 
import axios from 'axios'; // Importación corregida
import { Volume2, VolumeX, Heart, Type, Play, Pause, Brain, User, CheckCircle, X } from 'lucide-vue-next';
import SmartSubtitle from '@/components/SmartSubtitle.vue';

const props = defineProps({
    clip: Object,
    score: Number 
});

const emit = defineEmits(['open-quiz']);

// --- ESTADO ---
const videoRef = ref(null);
const currentTime = ref(0);
const isMuted = ref(false); // Sonido activo por defecto
const showSubs = ref(true);
const isPlaying = ref(false);
const pausedByInteraction = ref(false);
const isLiked = ref(props.clip.is_liked || false); // Estado inicial del corazón

// Estados para animaciones
const showTempPause = ref(false);
const showTempPlay = ref(false);

// --- LÓGICA DE CONTROL ---

const pauseVideo = () => {
    if (videoRef.value && !videoRef.value.paused) {
        videoRef.value.pause();
        isPlaying.value = false;
    }
};

const resumeVideo = () => {
    if (videoRef.value) {
        videoRef.value.play()
            .then(() => {
                isPlaying.value = true;
                pausedByInteraction.value = false;
            })
            .catch(e => {
                if (e.name === 'NotAllowedError') {
                    videoRef.value.muted = true;
                    isMuted.value = true;
                    videoRef.value.play();
                }
            });
    }
};

const handleWordInteraction = () => {
    pauseVideo();
    pausedByInteraction.value = true;
    showTempPause.value = true;
    setTimeout(() => showTempPause.value = false, 600); 
};

const handleOpenQuiz = () => {
    pauseVideo();
    emit('open-quiz');
};

defineExpose({ resume: resumeVideo });

// --- SUBTÍTULOS (Lógica Limpia - El Controller ya normalizó los tiempos) ---
const currentSubtitleText = computed(() => {
    const rawData = props.clip.transcript_json || props.clip.transcript;
    if (!rawData || !showSubs.value) return null;

    const segments = Array.isArray(rawData) ? rawData : (rawData.segments || []);
    const now = currentTime.value;

    const activeLine = segments.find(line => {
        const start = parseFloat(line.start);
        const end = parseFloat(line.end);
        // Margen de 0.2s para suavidad
        return now >= start && now <= (end + 0.2);
    });

    return activeLine ? activeLine.text : null;
});

const handleTimeUpdate = (event) => { 
    if (event && event.target) {
        currentTime.value = event.target.currentTime; 
    }
};

const toggleMute = (e) => { 
    e?.stopPropagation(); 
    if (videoRef.value) { 
        videoRef.value.muted = !videoRef.value.muted; 
        isMuted.value = videoRef.value.muted; 
    }
};

const toggleSubs = (e) => { 
    e?.stopPropagation(); 
    showSubs.value = !showSubs.value; 
};

const togglePlay = () => {
    if (!videoRef.value) return;
    if (videoRef.value.paused) {
        resumeVideo();
        showTempPlay.value = true;
        setTimeout(() => showTempPlay.value = false, 600);
    } else {
        pauseVideo();
        pausedByInteraction.value = false;
    }
};

const toggleLike = async (e) => {
    e?.stopPropagation();
    try {
        const response = await axios.post(route('flick.like', props.clip.id));
        isLiked.value = response.data.status === 'liked';
    } catch (error) {
        console.error("Error al dar like:", error);
    }
};

// --- CICLO DE VIDA ---
onMounted(() => { attemptAutoplay(); });

watch(() => props.clip, (newClip) => { 
    isPlaying.value = false; 
    currentTime.value = 0; 
    isLiked.value = newClip.is_liked || false; // Resetear corazón al cambiar clip
    if (videoRef.value) {
        videoRef.value.currentTime = 0;
    }
    setTimeout(attemptAutoplay, 100); 
});

const attemptAutoplay = () => {
    if (videoRef.value) {
        videoRef.value.muted = isMuted.value;
        const playPromise = videoRef.value.play();
        if (playPromise !== undefined) {
            playPromise.then(() => { 
                isPlaying.value = true; 
            }).catch(() => { 
                isPlaying.value = false; 
                videoRef.value.muted = true; 
                isMuted.value = true;
                videoRef.value.play(); 
            });
        }
    }
};
</script>

<template>
    <div class="relative w-full h-full bg-black overflow-hidden group">
        
        <div class="absolute top-6 left-6 z-30 flex items-center gap-2 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10 transition-all hover:bg-black/60">
            <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(250,204,21,0.8)]"></div>
            <span class="text-white font-bold text-sm font-mono tracking-widest">{{ score }} PTS</span>
        </div>
        <Link href="/dashboard" class="absolute top-6 right-6 z-30 p-2 bg-black/40 backdrop-blur-md rounded-full border border-white/10 text-white hover:bg-white/20 transition hover:scale-105 active:scale-95">
            <User :size="24" stroke-width="2.5" />
        </Link>

        <video 
            ref="videoRef"
            :key="clip.video_url"
            class="w-full h-full object-cover cursor-pointer" 
            :src="clip.video_url"
            loop :muted="isMuted" autoplay playsinline
            @timeupdate="handleTimeUpdate"
            @click="togglePlay"
        ></video>

        <div class="absolute inset-0 flex items-center justify-center z-40 pointer-events-none">
            <transition enter-active-class="transform transition ease-out duration-200" enter-from-class="opacity-0 scale-50" enter-to-class="opacity-100 scale-100" leave-active-class="transform transition ease-in duration-300" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-150">
                <div v-if="showTempPause" class="bg-black/40 text-white p-5 rounded-full backdrop-blur-md shadow-2xl">
                    <Pause class="w-12 h-12 fill-white" stroke-width="0" />
                </div>
            </transition>
            <transition enter-active-class="transform transition ease-out duration-200" enter-from-class="opacity-0 scale-50" enter-to-class="opacity-100 scale-100" leave-active-class="transform transition ease-in duration-300" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-150">
                <div v-if="showTempPlay" class="bg-black/40 text-white p-5 rounded-full backdrop-blur-md shadow-2xl">
                    <Play class="w-12 h-12 fill-white ml-1" stroke-width="0" />
                </div>
            </transition>
        </div>

        <div v-if="!isPlaying && !pausedByInteraction" class="absolute inset-0 flex items-center justify-center z-30 bg-black/40 backdrop-blur-[2px] cursor-pointer" @click="togglePlay">
            <div class="p-6 bg-white/20 rounded-full border-4 border-white backdrop-blur-md hover:scale-110 transition shadow-2xl animate-pulse">
                <Play :size="48" fill="white" class="text-white ml-1" />
            </div>
        </div>

        <div v-if="currentSubtitleText" class="absolute bottom-24 left-0 w-full pl-6 pr-24 z-20 flex justify-center pointer-events-none">
            <div class="pointer-events-auto max-w-3xl w-full text-center">
                <SmartSubtitle 
                    :text="currentSubtitleText" 
                    @word-clicked="handleWordInteraction" 
                    @popover-closed="resumeVideo"
                />
            </div>
        </div>

        <div class="absolute bottom-28 right-4 flex flex-col items-center gap-6 z-20">
            <button @click.stop="!clip.completed && $emit('open-quiz')" class="flex flex-col items-center gap-1 group transition" :class="clip.completed ? 'cursor-default' : 'cursor-pointer active:scale-95'">
                <div v-if="clip.completed && clip.won" class="p-3 bg-green-500/10 border border-green-500/50 rounded-full backdrop-blur-md text-green-400 shadow-[0_0_15px_rgba(74,222,128,0.2)] flex items-center justify-center">
                    <CheckCircle :size="24" stroke-width="2.5" />
                </div>
                <div v-else-if="clip.completed && !clip.won" class="p-3 bg-red-500/10 border border-red-500/50 rounded-full backdrop-blur-md text-red-500 shadow-[0_0_15px_rgba(239,68,68,0.2)] flex items-center justify-center">
                    <X :size="24" stroke-width="2.5" />
                </div>
                <div v-else class="p-3 bg-yellow-500 rounded-full text-black group-hover:scale-110 transition shadow-[0_0_15px_rgba(234,179,8,0.6)] animate-pulse flex items-center justify-center">
                    <Brain :size="26" stroke-width="2.5" />
                </div>
                <span class="text-xs font-bold drop-shadow-md" :class="{'text-green-400': clip.completed && clip.won, 'text-red-400': clip.completed && !clip.won, 'text-yellow-400': !clip.completed}">
                    {{ clip.completed ? (clip.won ? 'Genial' : 'Falló') : 'Quiz' }}
                </span>
            </button>

            <button @click="toggleLike" class="flex flex-col items-center gap-1 text-white group active:scale-90 transition">
                <div class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md transition shadow-lg"
                    :class="{'bg-red-500/20 border-red-500': isLiked}">
                    <Heart :size="24" 
                        stroke-width="2.5" 
                        :class="isLiked ? 'text-red-500 fill-red-500' : 'text-white'" 
                        class="transition-all duration-300" />
                </div>
                <span class="text-xs font-bold drop-shadow-md">{{ isLiked ? 'Liked' : 'Like' }}</span>
            </button>

            <button @click="toggleMute" class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md text-white hover:bg-white/30 transition shadow-lg active:scale-90">
                <VolumeX v-if="isMuted" :size="24" stroke-width="2.5" />
                <Volume2 v-else :size="24" stroke-width="2.5" />
            </button>

            <button @click="toggleSubs" class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md transition shadow-lg active:scale-90" :class="showSubs ? 'text-yellow-400 border-yellow-400/50' : 'text-white hover:bg-white/30'">
                <Type :size="24" stroke-width="2.5" />
            </button>
        </div>

        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 via-black/40 to-transparent pt-16 pb-6 px-6 z-10 pointer-events-none">
            <h3 class="font-bold text-xl text-white drop-shadow-md">{{ clip.title }}</h3>
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-0.5 bg-yellow-500 text-black text-xs font-bold rounded uppercase shadow-sm">{{ clip.difficulty }}</span>
                <p class="text-sm text-gray-200 truncate animate-pulse">Toca una palabra para guardarla.</p>
            </div>
        </div>
    </div>
</template>