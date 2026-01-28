<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
// 1. Layout y Iconos
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Mail, Lock } from 'lucide-vue-next';

// 2. Lógica original
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer Contraseña" />

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Nueva Contraseña</h2>
            <p class="text-gray-400 text-sm mt-2">Crea una contraseña segura para recuperar tu cuenta</p>
        </div>

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-5"
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
                        class="block w-full pl-12 pr-4 py-3 bg-black/40 border border-gray-700 rounded-xl text-gray-400 bg-gray-900 cursor-not-allowed sm:text-sm"
                        v-model="inputEmail"
                        readonly
                        autocomplete="email"
                    />
                </div>
                <div v-if="errors.email" class="text-red-400 text-xs mt-2 pl-1 flex items-center gap-1">
                    <span>⚠</span> {{ errors.email }}
                </div>
            </div>

            <div>
                <label for="password" class="block font-medium text-xs text-gray-400 uppercase tracking-wider mb-2">Nueva Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Lock class="h-5 w-5 text-gray-500 group-focus-within:text-yellow-400 transition-colors" />
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="block w-full pl-12 pr-4 py-3 bg-black/40 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 focus:bg-gray-900/80 transition-all duration-300 sm:text-sm"
                        placeholder="Mínimo 8 caracteres"
                        autofocus
                        autocomplete="new-password"
                    />
                </div>
                <div v-if="errors.password" class="text-red-400 text-xs mt-2 pl-1">{{ errors.password }}</div>
            </div>

            <div>
                <label for="password_confirmation" class="block font-medium text-xs text-gray-400 uppercase tracking-wider mb-2">Confirmar Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Lock class="h-5 w-5 text-gray-500 group-focus-within:text-yellow-400 transition-colors" />
                    </div>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="block w-full pl-12 pr-4 py-3 bg-black/40 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 focus:bg-gray-900/80 transition-all duration-300 sm:text-sm"
                        placeholder="Repite la contraseña"
                        autocomplete="new-password"
                    />
                </div>
                <div v-if="errors.password_confirmation" class="text-red-400 text-xs mt-2 pl-1">{{ errors.password_confirmation }}</div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                    :class="{ 'opacity-75 cursor-not-allowed': processing }"
                    :disabled="processing"
                >
                    <span v-if="processing">Guardando...</span>
                    <span v-else>RESTABLECER CONTRASEÑA</span>
                </button>
            </div>
        </Form>
    </GuestLayout>
</template>