<script setup>
import { Head } from '@inertiajs/vue3';
import { CheckCircle, Trophy } from 'lucide-vue-next';

// Aquí podrías recibir props como "totalPoints" si lo envías desde el backend
</script>

<template>
    <Head title="¡Todo listo!" />

    <div class="h-screen w-full bg-gray-900 flex justify-center items-center overflow-hidden">
        <div class="w-full max-w-md h-full bg-black relative shadow-2xl flex flex-col items-center justify-center p-8 text-center">
            
            <div class="relative mb-8">
                <div class="absolute inset-0 bg-green-500 blur-3xl opacity-20 rounded-full animate-pulse"></div>
                <Trophy :size="80" class="text-yellow-400 relative z-10 drop-shadow-[0_0_15px_rgba(250,204,21,0.5)]" />
            </div>

            <h1 class="text-3xl font-bold text-white mb-2">¡Estás al día!</h1>
            <p class="text-gray-400 text-lg mb-8">
                Has visto todos los videos disponibles por hoy.
            </p>

            <div class="bg-gray-800/50 p-6 rounded-2xl border border-white/10 w-full mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <CheckCircle class="text-green-400" :size="20" />
                    <span class="text-white font-medium">Videos completados</span>
                </div>
                <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-green-400 w-full"></div>
                </div>
                <p class="text-right text-xs text-gray-500 mt-2">100%</p>
            </div>

            <button 
                disabled
                class="w-full py-4 rounded-xl bg-gray-700 text-gray-400 font-bold cursor-not-allowed opacity-50"
            >
                Vuelve mañana por más
            </button>

            <p class="mt-8 text-xs text-gray-600">
                Flick v1.0 - Learning App
            </p>
        </div>
    </div>
</template>