<script setup>
import { Check } from 'lucide-vue-next';

defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: 'Acción completada exitosamente'
    }
});
</script>

<template>
    <transition 
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed top-20 right-4 sm:top-24 sm:right-8 z-[100] flex items-center gap-3 bg-gray-800 border border-gray-700 shadow-xl rounded-xl px-4 py-3 min-w-[200px] pointer-events-none">
            <div class="bg-yellow-400/20 p-1.5 rounded-full text-yellow-400 flex-shrink-0">
                <Check class="w-4 h-4" stroke-width="3" />
            </div>
            <span class="text-sm font-medium text-white">{{ message }}</span>
        </div>
    </transition>
</template>