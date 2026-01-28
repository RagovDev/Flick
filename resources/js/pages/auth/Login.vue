<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
// 1. Importamos Layout y Iconos
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Mail, Lock, Check } from 'lucide-vue-next';

// 2. Importamos la lógica original
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white tracking-tight">Bienvenido</h2>
            <p class="text-gray-400 text-sm mt-2">Ingresa tus credenciales para continuar</p>
        </div>

        <div 
            v-if="status" 
            class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm font-medium text-center"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
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
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="email"
                    />
                </div>
                <div v-if="errors.email" class="text-red-400 text-xs mt-2 pl-1 flex items-center gap-1">
                    <span>⚠</span> {{ errors.email }}
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block font-medium text-xs text-gray-400 uppercase tracking-wider">Contraseña</label>
                    <Link
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-gray-500 hover:text-yellow-400 transition duration-300"
                        tabindex="5"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>
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
                        tabindex="2"
                        autocomplete="current-password"
                    />
                </div>
                <div v-if="errors.password" class="text-red-400 text-xs mt-2 pl-1">{{ errors.password }}</div>
            </div>

            <div class="flex items-center justify-between">
                <label for="remember" class="flex items-center cursor-pointer group">
                    <div class="relative flex items-center">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            tabindex="3"
                            class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-gray-600 bg-gray-900 transition-all checked:border-yellow-400 checked:bg-yellow-400 hover:border-yellow-400"
                        />
                        <Check class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-black opacity-0 peer-checked:opacity-100 pointer-events-none" stroke-width="3.5" />
                    </div>
                    <span class="ms-2 text-sm text-gray-400 group-hover:text-gray-300 transition">Recordarme</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                :class="{ 'opacity-75 cursor-not-allowed': processing }"
                :disabled="processing"
                tabindex="4"
            >
                <span v-if="processing">Entrando...</span>
                <span v-else>INICIAR SESIÓN</span>
            </button>

            <div 
                v-if="canRegister" 
                class="mt-8 pt-6 border-t border-gray-800 text-center"
            >
                <span class="text-sm text-gray-500">¿No tienes cuenta? </span>
                <Link 
                    :href="register()" 
                    class="text-sm text-yellow-400 hover:text-yellow-300 font-bold ml-1 hover:underline transition"
                    tabindex="5"
                >
                    Regístrate gratis
                </Link>
            </div>
        </Form>
    </GuestLayout>
</template>