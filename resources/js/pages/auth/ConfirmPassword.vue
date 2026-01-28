<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
// 1. Importamos el Layout oscuro y los iconos
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Lock } from 'lucide-vue-next';

// 2. Mantenemos la lógica original exacta del store
import { store } from '@/routes/password/confirm';
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar Acceso" />

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Zona Segura 🔒</h2>
            <p class="text-gray-400 text-sm mt-3 leading-relaxed">
                Esta es un área protegida de la aplicación. Por favor, confirma tu contraseña para continuar.
            </p>
        </div>

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div>
                <label for="password" class="block font-medium text-xs text-gray-400 uppercase tracking-wider mb-2">Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Lock class="h-5 w-5 text-gray-500 group-focus-within:text-yellow-400 transition-colors" />
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="block w-full pl-12 pr-4 py-3 bg-black/40 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 focus:bg-gray-900/80 transition-all duration-300 sm:text-sm"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        autofocus
                    />
                </div>
                <div v-if="errors.password" class="text-red-400 text-xs mt-2 pl-1 flex items-center gap-1">
                    <span>⚠</span> {{ errors.password }}
                </div>
            </div>

            <button
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                :class="{ 'opacity-75 cursor-not-allowed': processing }"
                :disabled="processing"
            >
                <span v-if="processing">Confirmando...</span>
                <span v-else>CONFIRMAR ACCESO</span>
            </button>
        </Form>
    </GuestLayout>
</template>