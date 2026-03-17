<script setup>
import { ref, computed } from 'vue'; // 🌟 Agregamos computed
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X, Home, LayoutDashboard, UploadCloud, LogOut, User as UserIcon, BookOpen, PlayCircle, Film } from 'lucide-vue-next';

const showingNavigationDropdown = ref(false);
const page = usePage();

// 🌟 MAGIA REACTIVA: Ahora si el usuario gana puntos, el navbar se actualiza al instante
const user = computed(() => page.props.auth.user);
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-white font-sans selection:bg-yellow-400 selection:text-black">
        
        <nav class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                                <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center shadow-[0_0_10px_rgba(250,204,21,0.4)] group-hover:scale-105 transition">
                                    <span class="text-lg font-black text-black">F</span>
                                </div>
                                <span class="font-bold text-xl tracking-tight hidden sm:block">Flick</span>
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            
                            <Link :href="route('flick.index')" :class="['inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out', route().current('flick.index') ? 'border-yellow-400 text-white' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500']">
                                <PlayCircle class="w-4 h-4 mr-2" />
                                Feed
                            </Link>

                            <Link :href="route('dashboard')" :class="['inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out', route().current('dashboard') ? 'border-yellow-400 text-white' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500']">
                                <Home class="w-4 h-4 mr-2" />
                                Mi Panel
                            </Link>

                            <Link :href="route('vocabulary.index')" :class="['inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out', route().current('vocabulary.index') ? 'border-yellow-400 text-white' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500']">
                                <BookOpen class="w-4 h-4 mr-2" />
                                Vocabulario
                            </Link>
                            
                            <template v-if="user.is_admin">
                                <Link :href="route('admin.dashboard')" :class="['inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out', route().current('admin.dashboard') ? 'border-yellow-400 text-white' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500']">
                                    <LayoutDashboard class="w-4 h-4 mr-2" />
                                    Admin
                                </Link>
                                
                                <Link :href="route('admin.clips.index')" :class="['inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out', route().current('admin.clips.index') ? 'border-yellow-400 text-white' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500']">
                                    <Film class="w-4 h-4 mr-2" />
                                    Biblioteca
                                </Link>

                                <Link :href="route('admin.clips.create')" :class="['inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out', route().current('admin.clips.create') ? 'border-yellow-400 text-white' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500']">
                                    <UploadCloud class="w-4 h-4 mr-2" />
                                    Subir Video
                                </Link>
                            </template>
                            
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="flex items-center gap-4"> 
                            <div class="text-sm font-bold text-yellow-400 flex items-center gap-1 bg-yellow-400/10 px-3 py-1.5 rounded-full border border-yellow-400/20 shadow-inner">
                                🏆 {{ user.score || 0 }} pts
                            </div>
                            
                            <div class="h-6 w-px bg-gray-700 mx-1"></div>
                            
                            <div class="flex items-center gap-2 cursor-pointer group">
                                <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center border border-gray-600 transition group-hover:border-gray-500 overflow-hidden">
                                    <UserIcon class="w-4 h-4 text-gray-400" />
                                </div>
                                <span class="text-sm font-medium text-gray-300 group-hover:text-white transition">{{ user.name }}</span>
                            </div>

                            <Link 
                                :href="route('logout')" 
                                method="post" 
                                as="button" 
                                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded-full transition-all duration-200 focus:outline-none"
                                title="Cerrar sesión"
                            >
                                <LogOut :size="20" stroke-width="2.5" />
                            </Link>
                            
                        </div>
                    </div>

                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
                            <Menu v-if="!showingNavigationDropdown" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden bg-gray-800 border-t border-gray-700 absolute w-full shadow-2xl">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('flick.index')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out" :class="route().current('flick.index') ? 'border-yellow-400 text-white bg-gray-700/50' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700'">Feed de Videos</Link>
                    
                    <Link :href="route('dashboard')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out" :class="route().current('dashboard') ? 'border-yellow-400 text-white bg-gray-700/50' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700'">Mi Panel</Link>
                    
                    <Link :href="route('vocabulary.index')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out" :class="route().current('vocabulary.index') ? 'border-yellow-400 text-white bg-gray-700/50' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700'">Vocabulario</Link>
                    
                    <template v-if="user.is_admin">
                        <Link :href="route('admin.dashboard')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out" :class="route().current('admin.dashboard') ? 'border-yellow-400 text-white bg-gray-700/50' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700'">Panel Admin</Link>
                        
                        <Link :href="route('admin.clips.index')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out" :class="route().current('admin.clips.index') ? 'border-yellow-400 text-white bg-gray-700/50' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700'">Biblioteca</Link>

                        <Link :href="route('admin.clips.create')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out" :class="route().current('admin.clips.create') ? 'border-yellow-400 text-white bg-gray-700/50' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700'">Subir Video</Link>
                    </template>
                </div>
                
                <div class="pt-4 pb-1 border-t border-gray-700 bg-gray-900/50">
                    <div class="flex items-center justify-between px-4 mb-2">
                        <div class="font-medium text-base text-gray-200">{{ user.name }}</div>
                        <div class="font-medium text-sm text-yellow-400">🏆 {{ user.score || 0 }} pts</div>
                    </div>
                    <div class="space-y-1">
                        <Link :href="route('logout')" method="post" as="button" class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-red-400 hover:text-white hover:bg-red-500 transition duration-150 ease-in-out">
                            Cerrar Sesión
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-gray-900 shadow border-b border-gray-800" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
        
    </div>
</template>