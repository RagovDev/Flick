<script setup>
import { ref, computed, onMounted, watch } from 'vue';
// Importamos todos los iconos necesarios, incluido 'Brain'
import { Volume2, VolumeX, Heart, Type, Play, Brain } from 'lucide-vue-next';

const props = defineProps({
    clip: Object,
});

// Definimos el evento para avisarle al padre (Player.vue) que abra el quiz
const emit = defineEmits(['open-quiz']);

const videoRef = ref(null);
const currentTime = ref(0);
const isMuted = ref(true); // Iniciamos muteado para mejorar probabilidad de autoplay
const showSubs = ref(true);
const isPlaying = ref(false); // Estado para saber si mostrar el botón de Play

// Lógica de Subtítulos: Busca qué texto corresponde al segundo actual
const currentSubtitle = computed(() => {
    if (!props.clip.transcript_json || !showSubs.value) return null;
    
    return props.clip.transcript_json.find(
        line => currentTime.value >= line.start && currentTime.value <= line.end
    );
});

// Actualizar tiempo actual del video
const handleTimeUpdate = () => {
    if (videoRef.value) currentTime.value = videoRef.value.currentTime;
};

// Toggle Mute
const toggleMute = (e) => {
    e?.stopPropagation(); // Evitar que el click pause el video si se hace click en el botón
    if (videoRef.value) {
        videoRef.value.muted = !videoRef.value.muted;
        isMuted.value = videoRef.value.muted;
    }
};

// Toggle Subs
const toggleSubs = (e) => {
    e?.stopPropagation();
    showSubs.value = !showSubs.value;
};

// Lógica Principal de Play/Pause
const togglePlay = () => {
    if (!videoRef.value) return;

    if (videoRef.value.paused) {
        videoRef.value.play()
            .then(() => isPlaying.value = true)
            .catch(e => console.error("Error al reproducir:", e));
    } else {
        videoRef.value.pause();
        isPlaying.value = false;
    }
};

// Intentar Autoplay al montar o cambiar de video
onMounted(() => {
    attemptAutoplay();
});

// Si cambia el clip (props), re-intentar autoplay
watch(() => props.clip, () => {
    isPlaying.value = false; // Resetear estado visual
    setTimeout(attemptAutoplay, 100); // Pequeño delay para que el DOM actualice
});

const attemptAutoplay = () => {
    if (videoRef.value) {
        const playPromise = videoRef.value.play();
        
        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    // Autoplay exitoso
                    isPlaying.value = true;
                })
                .catch(() => {
                    // Autoplay bloqueado por el navegador
                    console.log("Autoplay bloqueado. Esperando interacción.");
                    isPlaying.value = false;
                    videoRef.value.muted = true; // Asegurar mute
                });
        }
    }
};
</script>

<template>
    <div class="relative w-full h-full bg-black overflow-hidden group">
        
        <video 
            ref="videoRef"
            :key="clip.video_url"
            class="w-full h-full object-cover cursor-pointer" 
            :src="clip.video_url"
            loop
            muted
            autoplay
            playsinline
            @timeupdate="handleTimeUpdate"
            @click="togglePlay"
        ></video>

        <div 
            v-if="!isPlaying" 
            class="absolute inset-0 flex items-center justify-center z-30 bg-black/40 backdrop-blur-[2px] cursor-pointer transition-all duration-300"
            @click="togglePlay"
        >
            <div class="p-6 bg-white/20 rounded-full border-4 border-white backdrop-blur-md hover:scale-110 transition shadow-2xl animate-pulse">
                <Play :size="48" fill="white" class="text-white ml-1" />
            </div>
        </div>

        <div v-if="currentSubtitle" class="absolute bottom-32 left-0 w-full pl-6 pr-24 pointer-events-none z-10 transition-all duration-200">
            <span class="inline-block bg-black/60 backdrop-blur-md text-white text-lg font-bold px-4 py-3 rounded-2xl shadow-2xl leading-snug">
                <span 
                    v-for="(word, index) in currentSubtitle.text.split(' ')" 
                    :key="index"
                    class="pointer-events-auto cursor-pointer hover:text-yellow-400 hover:scale-110 inline-block transition-all duration-100 mr-1.5"
                >
                    {{ word }}
                </span>
            </span>
        </div>

        <div class="absolute bottom-28 right-4 flex flex-col items-center gap-6 z-20">
            
            <button 
                @click.stop="$emit('open-quiz')" 
                class="flex flex-col items-center gap-1 text-white group cursor-pointer transition active:scale-95"
            >
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
                <span class="px-2 py-0.5 bg-yellow-500 text-black text-xs font-bold rounded uppercase shadow-sm">
                    {{ clip.difficulty }}
                </span>
                <p class="text-sm text-gray-200 truncate">Toca una palabra para aprender.</p>
            </div>
        </div>
    </div>
</template>