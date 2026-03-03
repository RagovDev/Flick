<script setup>
import { Link, router } from '@inertiajs/vue3';
import { LogOut, User } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const logout = () => {
    // Usamos la URL directa para evitar errores de 'route'
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-900">
        <nav class="border-b border-gray-700 bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('dashboard')">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-400 font-bold text-black"
                                >
                                    F
                                </div>
                            </Link>
                        </div>

                        <div
                            class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"
                        >
                            <Link
                                :href="route('dashboard')"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm leading-5 font-medium transition duration-150 ease-in-out focus:outline-none"
                                :class="
                                    $page.url === '/dashboard'
                                        ? 'border-yellow-400 text-white'
                                        : 'border-transparent text-gray-300 hover:border-gray-300 hover:text-white'
                                "
                            >
                                Dashboard
                            </Link>

                            <Link
                                :href="route('flick.index')"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm leading-5 font-medium transition duration-150 ease-in-out focus:outline-none"
                                :class="
                                    $page.url === '/flick'
                                        ? 'border-yellow-400 text-white'
                                        : 'border-transparent text-gray-300 hover:border-gray-300 hover:text-white'
                                "
                            >
                                Reproductor
                            </Link>

                            <Link
                                :href="route('vocabulary.index')"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm leading-5 font-medium transition duration-150 ease-in-out focus:outline-none"
                                :class="
                                    $page.url === '/vocabulary'
                                        ? 'border-yellow-400 text-white'
                                        : 'border-transparent text-gray-300 hover:border-gray-300 hover:text-white'
                                "
                            >
                                Mi Vocabulario
                            </Link>

                            <Link
                                :href="route('admin.create')"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm leading-5 font-medium transition duration-150 ease-in-out focus:outline-none"
                                :class="
                                    $page.url === '/admin/upload'
                                        ? 'border-yellow-400 text-white'
                                        : 'border-transparent text-gray-300 hover:border-gray-300 hover:text-white'
                                "
                            >
                                Upload
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="relative ml-3 flex items-center gap-4">
                            <span class="mr-2 text-sm text-gray-400">
                                {{ $page.props.auth?.user?.name || 'Usuario' }}
                            </span>

                            <button
                                @click="logout"
                                class="text-gray-400 transition hover:text-white"
                                title="Cerrar Sesión"
                            >
                                <LogOut :size="20" />
                            </button>

                            <div
                                class="rounded-full bg-gray-700 p-2 text-gray-200"
                            >
                                <User :size="20" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-gray-800 shadow" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
