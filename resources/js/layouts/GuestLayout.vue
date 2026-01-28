<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gray-800 via-gray-900 to-black relative overflow-hidden">
        
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-1/2 bg-yellow-500/10 blur-[120px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 mb-8">
            <Link href="/">
                <div class="w-20 h-20 bg-yellow-400 rounded-2xl flex items-center justify-center shadow-[0_0_30px_rgba(250,204,21,0.3)] transform hover:scale-105 hover:rotate-3 transition duration-500 ease-out">
                    <span class="text-4xl font-black text-black">F</span>
                </div>
            </Link>
        </div>

        <div class="relative z-10 w-full sm:max-w-md px-8 py-10 bg-gray-900/60 backdrop-blur-xl border border-white/10 shadow-2xl overflow-hidden sm:rounded-3xl">
            <slot />
        </div>
        
        <div class="mt-8 text-gray-600 text-xs relative z-10">
            &copy; 2026 Flick Learning.
        </div>
    </div>
</template>