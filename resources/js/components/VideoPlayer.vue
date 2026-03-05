<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3'; 
import { Volume2, VolumeX, Heart, Type, Play, Pause, Brain, User, CheckCircle, X } from 'lucide-vue-next';
import SmartSubtitle from '@/components/SmartSubtitle.vue';
import LikeButton from '@/components/LikeButton.vue';

// IMPORTAMOS NUESTRA VARIABLE GLOBAL
import { useAudio } from '@/composables/useAudio';

const props = defineProps({
    clip: Object,
    score: Number 
});

const emit = defineEmits(['open-quiz']);

// Extraemos la variable global
const { isGlobalMuted } = useAudio();

const videoRef = ref(null);
const currentTime = ref(0);
const showSubs = ref(true);
const isPlaying = ref(false);
const pausedByInteraction = ref(false);

const showTempPause = ref(false);
const showTempPlay = ref(false);

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
                // Fallback de seguridad del navegador
                if (e.name === 'NotAllowedError') {
                    videoRef.value.muted = true;
                    isGlobalMuted.value = true; // Forzamos el estado global a silencio si el navegador lo bloquea
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

defineExpose({ resume: resumeVideo });

const currentSubtitleText = computed(() => {
    const rawData = props.clip.transcript_json || props.clip.transcript;
    if (!rawData || !showSubs.value) return null;

    const segments = Array.isArray(rawData) ? rawData : (rawData.segments || []);
    const now = currentTime.value;

    const activeLine = segments.find(line => {
        const start = parseFloat(line.start);
        const end = parseFloat(line.end);
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
        isGlobalMuted.value = videoRef.value.muted; // Actualiza el estado global al tocar el botón
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

const handleLikeUpdate = ({ isLiked, count }) => {
    props.clip.is_liked = isLiked;
    props.clip.likes_count = count;
};

onMounted(() => { attemptAutoplay(); });

watch(() => props.clip, () => { 
    isPlaying.value = false; 
    currentTime.value = 0; 
    if (videoRef.value) {
        videoRef.value.currentTime = 0;
    }
    setTimeout(attemptAutoplay, 100); 
});

const attemptAutoplay = () => {
    if (videoRef.value) {
        // Al cargar un video nuevo, le aplicamos el estado global de audio
        videoRef.value.muted = isGlobalMuted.value;
        
        const playPromise = videoRef.value.play();
        if (playPromise !== undefined) {
            playPromise.then(() => { 
                isPlaying.value = true; 
            }).catch(() => { 
                isPlaying.value = false; 
                videoRef.value.muted = true; 
                isGlobalMuted.value = true; // Si el navegador lo bloquea, lo silenciamos globalmente
                videoRef.value.play(); 
            });
        }
    }
};
</script>

<template>
    <div class="relative w-full h-full bg-black overflow-hidden group">
        
        <div class="absolute top-6 left-6 z-30 flex items-center gap-2 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10 transition-all hover:bg-black/60 shadow-lg">
            <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(250,204,21,0.8)]"></div>
            <span class="text-white font-bold text-sm font-mono tracking-widest">{{ score }} PTS</span>
        </div>
        <Link href="/dashboard" class="absolute top-6 right-6 z-30 p-2 bg-black/40 backdrop-blur-md rounded-full border border-white/10 text-white hover:bg-white/20 transition hover:scale-105 active:scale-95 shadow-lg">
            <User :size="24" stroke-width="2.5" />
        </Link>

        <transition 
            enter-active-class="transition-opacity duration-500 ease-in-out" 
            enter-from-class="opacity-0" 
            enter-to-class="opacity-100"
        >
            <video 
                ref="videoRef"
                :key="clip.video_url"
                class="w-full h-[70vh] object-cover cursor-pointer my-auto absolute top-1/2 -translate-y-1/2" 
                :src="clip.video_url"
                loop :muted="isGlobalMuted" autoplay playsinline
                @timeupdate="handleTimeUpdate"
                @click="togglePlay"
            ></video>
        </transition>

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

        <div v-if="currentSubtitleText" class="absolute bottom-32 left-6 right-20 z-20 flex justify-start pointer-events-none drop-shadow-lg">
            <div class="pointer-events-auto max-w-[85%] sm:max-w-[75%]">
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

            <LikeButton 
                :key="clip.id" 
                :clip-id="clip.id" 
                :initial-is-liked="clip.is_liked" 
                :initial-count="clip.likes_count" 
                @like-toggled="handleLikeUpdate" 
            />

            <button @click="toggleMute" class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md text-white hover:bg-white/30 transition shadow-lg active:scale-90">
                <VolumeX v-if="isGlobalMuted" :size="24" stroke-width="2.5" />
                <Volume2 v-else :size="24" stroke-width="2.5" />
            </button>

            <button @click="toggleSubs" class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md transition shadow-lg active:scale-90" :class="showSubs ? 'text-yellow-400 border-yellow-400/50' : 'text-white hover:bg-white/30'">
                <Type :size="24" stroke-width="2.5" />
            </button>
        </div>

        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/95 via-black/50 to-transparent pt-32 pb-8 px-6 z-10 pointer-events-none transition-opacity duration-300">
            <h3 class="font-bold text-xl text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] line-clamp-2 leading-tight">{{ clip.title }}</h3>
            <div class="flex items-center gap-3 mt-3">
                <span class="px-2.5 py-1 bg-yellow-500 text-black text-[10px] font-black rounded-md uppercase shadow-lg tracking-wider">{{ clip.difficulty }}</span>
                <p class="text-xs text-gray-300/80 truncate font-medium tracking-wide">Toca una palabra para guardarla.</p>
            </div>
        </div>
    </div>
</template>