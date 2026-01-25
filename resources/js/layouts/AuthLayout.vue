<script setup>
import { Link, router } from '@inertiajs/vue3';
import { User, LogOut } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const logout = () => {
    // Usamos la URL directa para evitar errores de 'route'
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-900">
        <nav class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')">
                                <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center font-bold text-black">
                                    F
                                </div>
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link 
                                :href="route('dashboard')" 
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                :class="$page.url === '/dashboard' ? 'border-yellow-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300'"
                            >
                                Dashboard
                            </Link>
                            
                            <Link 
                                :href="route('flick.index')" 
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                :class="$page.url === '/flick' ? 'border-yellow-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300'"
                            >
                                Reproductor
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative flex items-center gap-4">
                            <span class="text-gray-400 text-sm mr-2">
                                {{ $page.props.auth?.user?.name || 'Usuario' }}
                            </span>
                            
                            <button @click="logout" class="text-gray-400 hover:text-white transition" title="Cerrar Sesión">
                                <LogOut :size="20" />
                            </button>
                            
                            <div class="bg-gray-700 p-2 rounded-full text-gray-200">
                                <User :size="20" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-gray-800 shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>