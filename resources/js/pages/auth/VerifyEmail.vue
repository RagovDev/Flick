<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
// 1. Layout y Iconos
import GuestLayout from '@/layouts/GuestLayout.vue';
import { MailCheck } from 'lucide-vue-next';

// 2. Lógica Original
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Verificar Correo" />

        <div class="text-center mb-6">
            <div class="bg-yellow-400/10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border border-yellow-400/20">
                <MailCheck class="w-8 h-8 text-yellow-400" />
            </div>
            <h2 class="text-xl font-bold text-white">Verifica tu correo</h2>
        </div>

        <div class="mb-6 text-sm text-gray-400 text-center leading-relaxed">
            ¡Gracias por registrarte! Antes de empezar, verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar.
        </div>

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm font-medium text-center"
        >
            Se ha enviado un nuevo enlace de verificación a la dirección de correo que proporcionaste durante el registro.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <button
                type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-yellow-400/20 text-sm font-bold text-black bg-yellow-400 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-yellow-400 transition-all duration-300 transform hover:-translate-y-0.5"
                :class="{ 'opacity-75 cursor-not-allowed': processing }"
                :disabled="processing"
            >
                <span v-if="processing">Enviando...</span>
                <span v-else>REENVIAR EMAIL DE VERIFICACIÓN</span>
            </button>

            <Link
                :href="logout()"
                method="post"
                as="button"
                class="text-sm text-gray-500 hover:text-white underline decoration-gray-600 hover:decoration-white transition-all block mx-auto"
            >
                Cerrar Sesión
            </Link>
        </Form>
    </GuestLayout>
</template>