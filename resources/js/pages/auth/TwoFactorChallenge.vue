<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
// 1. Layout y Iconos
import GuestLayout from '@/layouts/GuestLayout.vue';
import { ShieldCheck, Key, ArrowRight, Smartphone } from 'lucide-vue-next';

// 2. Lógica Original
import { store } from '@/routes/two-factor/login';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');

// Computada para textos dinámicos (Traducidos al español)
const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Código de Recuperación',
            description: 'Confirma el acceso a tu cuenta introduciendo uno de tus códigos de emergencia.',
            toggleText: 'Usar código de autenticación',
        };
    }

    return {
        title: 'Autenticación de Dos Factores',
        description: 'Introduce el código de seguridad de 6 dígitos de tu aplicación de autenticación.',
        toggleText: 'Usar código de recuperación',
    };
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};
</script>

<template>
    <GuestLayout>
        <Head title="Autenticación 2FA" />

        <div class="text-center mb-8">
            <div class="bg-yellow-400/10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border border-yellow-400/20">
                <Key v-if="showRecoveryInput" class="w-8 h-8 text-yellow-400" />
                <Smartphone v-else class="w-8 h-8 text-yellow-400" />
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">{{ authConfigContent.title }}</h2>
            <p class="text-gray-400 text-sm mt-3 leading-relaxed px-4">
                {{ authConfigContent.description }}
            </p>
        </div>

        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    class="space-y-6"
                    reset-on-error
                    @error="code = ''"
                    #default="{ errors, processing, clearErrors }"
                >
                    <input type="hidden" name="code" :value="code" />

                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <ShieldCheck class="h-5 w-5 text-gray-500 group-focus-within:text-yellow-400 transition-colors" />
                            </div>
                            
                            <input
                                id="otp"
                                type="text"
                                v-model="code"
                                maxlength="6"
                                class="block w-full py-4 bg-black/40 border border-gray-700 rounded-xl text-white placeholder-gray-700 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 focus:bg-gray-900/80 transition-all duration-300 text-center text-2xl font-mono tracking-[0.5em] font-bold"
                                placeholder="000000"
                                :disabled="processing"
                                autofocus
                                autocomplete="one-time-code"
                            />
                        </div>
                        <div v-if="errors.code" class="text-red-400 text-xs mt-2 text-center flex justify-center items-center gap-1">
                            <span>⚠</span> {{ errors.code }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="group w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                        :class="{ 'opacity-75 cursor-not-allowed': processing }"
                        :disabled="processing"
                    >
                        <span v-if="processing">Verificando...</span>
                        <span v-else class="flex items-center gap-2">
                            CONTINUAR
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </span>
                    </button>

                    <div class="text-center">
                        <span class="text-xs text-gray-500">¿Problemas con la app? </span>
                        <button
                            type="button"
                            class="text-xs text-yellow-400 hover:text-yellow-300 font-bold ml-1 hover:underline transition"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>

            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-6"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                >
                    <div>
                        <label for="recovery_code" class="block font-medium text-xs text-gray-400 uppercase tracking-wider mb-2">Código de Emergencia</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Key class="h-5 w-5 text-gray-500 group-focus-within:text-yellow-400 transition-colors" />
                            </div>
                            <input
                                name="recovery_code"
                                type="text"
                                class="block w-full pl-12 pr-4 py-3 bg-black/40 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 focus:bg-gray-900/80 transition-all duration-300 sm:text-sm"
                                placeholder="Introduce tu código de recuperación"
                                :autofocus="showRecoveryInput"
                                required
                            />
                        </div>
                        <div v-if="errors.recovery_code" class="text-red-400 text-xs mt-2 pl-1 flex items-center gap-1">
                            <span>⚠</span> {{ errors.recovery_code }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                        :class="{ 'opacity-75 cursor-not-allowed': processing }"
                        :disabled="processing"
                    >
                        <span v-if="processing">Verificando...</span>
                        <span v-else>USAR CÓDIGO DE RECUPERACIÓN</span>
                    </button>

                    <div class="text-center">
                        <span class="text-xs text-gray-500">¿Ya tienes tu app? </span>
                        <button
                            type="button"
                            class="text-xs text-yellow-400 hover:text-yellow-300 font-bold ml-1 hover:underline transition"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </GuestLayout>
</template>