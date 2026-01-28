<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3'; // Agregamos Link para el botón de volver
// 1. Importamos Layout y Iconos
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Mail, ArrowLeft } from 'lucide-vue-next';

// 2. Mantenemos las importaciones de lógica originales
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar Contraseña" />

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Recuperar Acceso</h2>
            <p class="text-gray-400 text-sm mt-3 leading-relaxed">
                ¿Olvidaste tu contraseña? Ingresa tu correo y te enviaremos un enlace para restablecerla.
            </p>
        </div>

        <div 
            v-if="status" 
            class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm font-medium text-center flex items-center justify-center gap-2"
        >
            <span>✓</span> {{ status }}
        </div>

        <Form 
            v-bind="email.form()" 
            v-slot="{ errors, processing }" 
            class="space-y-6"
        >
            <div>
                <label for="email" class="block font-medium text-xs text-gray-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Mail class="h-5 w-5 text-gray-500 group-focus-within:text-yellow-400 transition-colors" />
                    </div>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="block w-full pl-12 pr-4 py-3 bg-black/40 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 focus:bg-gray-900/80 transition-all duration-300 sm:text-sm"
                        placeholder="tu@correo.com"
                        autocomplete="off"
                        autofocus
                    />
                </div>
                <div v-if="errors.email" class="text-red-400 text-xs mt-2 pl-1 flex items-center gap-1">
                    <span>⚠</span> {{ errors.email }}
                </div>
            </div>

            <div class="pt-2">
                <button
                    class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                    :class="{ 'opacity-75 cursor-not-allowed': processing }"
                    :disabled="processing"
                >
                    <span v-if="processing">Enviando...</span>
                    <span v-else>ENVIAR ENLACE DE RECUPERACIÓN</span>
                </button>
            </div>
        </Form>

        <div class="mt-6 text-center">
            <Link 
                :href="login()" 
                class="inline-flex items-center text-sm text-gray-500 hover:text-white transition duration-300 group"
            >
                <ArrowLeft class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                Volver al inicio de sesión
            </Link>
        </div>
    </GuestLayout>
</template>