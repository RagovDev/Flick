<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3'; 
import { Volume2, VolumeX, Heart, Type, Play, Brain, User } from 'lucide-vue-next';
import SmartSubtitle from '@/components/SmartSubtitle.vue';

const props = defineProps({
    clip: Object,
    score: Number 
});

const emit = defineEmits(['open-quiz']);

// Referencias y Estado
const videoRef = ref(null);
const currentTime = ref(0);
const isMuted = ref(true); 
const showSubs = ref(true);
const isPlaying = ref(false);

// NUEVO: Estado para saber si pausamos para estudiar una palabra
// Si es true, NO mostramos el botón gigante de Play
const pausedByInteraction = ref(false);

// --- LÓGICA DE CONTROL DE VIDEO ---

// 1. Pausar video (Interno)
const pauseVideo = () => {
    if (videoRef.value && !videoRef.value.paused) {
        videoRef.value.pause();
        isPlaying.value = false;
    }
};

// 2. Reanudar video (Expuesto al padre)
const resumeVideo = () => {
    if (videoRef.value) {
        videoRef.value.play()
            .then(() => {
                isPlaying.value = true;
                pausedByInteraction.value = false; // Ya no estamos en "modo estudio"
            })
            .catch(e => console.error("Error al reanudar:", e));
    }
};

// 3. Manejar clic en palabra (Pausa Limpia)
const handleWordInteraction = () => {
    pauseVideo();
    pausedByInteraction.value = true; // Ocultamos el botón Play gigante para leer tranquilos
};

// 4. Manejar apertura de Quiz (Pausa + Emit)
const handleOpenQuiz = () => {
    pauseVideo();
    emit('open-quiz');
};

// IMPORTANTE: Exponemos 'resume' para que Player.vue pueda usarlo
defineExpose({ resume: resumeVideo });

// --- LÓGICA DE SUBTÍTULOS ---
const currentSubtitleText = computed(() => {
    const transcriptData = props.clip.transcript || props.clip.transcript_json;
    
    if (!transcriptData || !showSubs.value || !Array.isArray(transcriptData)) return null;
    
    const activeLine = transcriptData.find(line => 
        currentTime.value >= parseFloat(line.start) && 
        currentTime.value <= parseFloat(line.end)
    );

    return activeLine ? activeLine.text : null;
});

// --- EVENT HANDLERS ---
const handleTimeUpdate = () => {
    if (videoRef.value) currentTime.value = videoRef.value.currentTime;
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
        resumeVideo(); // Usamos nuestra función centralizada
    } else {
        pauseVideo();
        // Si el usuario pausa manualmente, SÍ queremos mostrar el botón Play
        pausedByInteraction.value = false; 
    }
};

// Autoplay
onMounted(() => { attemptAutoplay(); });

watch(() => props.clip, () => {
    isPlaying.value = false; 
    setTimeout(attemptAutoplay, 100); 
});

const attemptAutoplay = () => {
    if (videoRef.value) {
        const playPromise = videoRef.value.play();
        if (playPromise !== undefined) {
            playPromise
                .then(() => { isPlaying.value = true; })
                .catch(() => {
                    // Autoplay bloqueado
                    isPlaying.value = false;
                    videoRef.value.muted = true;
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

        <Link href="/dashboard" class="absolute top-6 right-6 z-30 p-2 bg-black/40 backdrop-blur-md rounded-full border border-white/10 text-white hover:bg-white/20 transition hover:scale-105 active:scale-95" title="Ir a mi perfil">
            <User :size="24" stroke-width="2.5" />
        </Link>

        <video 
            ref="videoRef"
            :key="clip.video_url"
            class="w-full h-full object-cover cursor-pointer" 
            :src="clip.video_url"
            loop muted autoplay playsinline
            @timeupdate="handleTimeUpdate"
            @click="togglePlay"
        ></video>

        <div 
            v-if="!isPlaying && !pausedByInteraction" 
            class="absolute inset-0 flex items-center justify-center z-30 bg-black/40 backdrop-blur-[2px] cursor-pointer transition-all duration-300"
            @click="togglePlay"
        >
            <div class="p-6 bg-white/20 rounded-full border-4 border-white backdrop-blur-md hover:scale-110 transition shadow-2xl animate-pulse">
                <Play :size="48" fill="white" class="text-white ml-1" />
            </div>
        </div>

        <div v-if="currentSubtitleText" class="absolute bottom-24 left-0 w-full pl-6 pr-24 z-20 flex justify-center pointer-events-none">
            <div class="pointer-events-auto max-w-3xl w-full text-center">
                <SmartSubtitle 
                    :text="currentSubtitleText" 
                    @word-clicked="handleWordInteraction" 
                />
            </div>
        </div>

        <div class="absolute bottom-28 right-4 flex flex-col items-center gap-6 z-20">
            
            <button @click.stop="handleOpenQuiz" class="flex flex-col items-center gap-1 text-white group cursor-pointer transition active:scale-95">
                <div class="p-3 bg-yellow-500 rounded-full text-black hover:scale-110 transition shadow-[0_0_15px_rgba(234,179,8,0.6)] animate-pulse">
                    <Brain :size="26" stroke-width="2.5" />
                </div>
                <span class="text-xs font-bold drop-shadow-md text-yellow-400">Quiz</span>
            </button>

            <button class="flex flex-col items-center gap-1 text-white group active:scale-90 transition">
                <div class="p-3 bg-white/10 border border-white/20 rounded-full backdrop-blur-md group-hover:bg-red-500/20 group-hover:border-red-500 transition shadow-lg">
                    <Heart :size="24" stroke-width="2.5" class="group-hover:text-red-500 transition" />
                </div>
                <span class="text-xs font-bold drop-shadow-md">1.2k</span>
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