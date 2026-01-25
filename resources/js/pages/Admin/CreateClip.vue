<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { route } from 'ziggy-js';

// Definimos el formulario
const form = useForm({
    title: '',
    video_file: null,
    transcript_json: '', 
    question_text: '',
    correct_option: '',
    wrong_option_1: '',
    wrong_option_2: '',
});

const submit = () => {
    // AHORA SÍ: Usamos route() correctamente.
    // Esto es mantenible: si cambias la URL en web.php, esto sigue funcionando.
    form.post(route('admin.store'), {
        forceFormData: true, 
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Creator Studio" />

    <AuthLayout>
        <template #header>
            <h2 class="font-bold text-xl text-yellow-400 leading-tight tracking-wide uppercase text-center">
                ⚡ Creator Studio
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-gray-800 border border-gray-700 shadow-2xl sm:rounded-2xl p-8 backdrop-blur-sm">
                    
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-white border-b border-gray-700 pb-2">1. El Contenido</h3>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-400 mb-2">Título del Video</label>
                                <input 
                                    v-model="form.title" 
                                    type="text" 
                                    class="w-full bg-gray-900 border border-gray-700 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition placeholder-gray-600" 
                                    placeholder="Ej: Tráiler de Matrix - Escena del Lobby"
                                >
                                <div v-if="form.errors.title" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.title }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-400 mb-2">Archivo MP4</label>
                                <div class="relative group">
                                    <input 
                                        @input="form.video_file = $event.target.files[0]" 
                                        type="file" 
                                        accept="video/mp4" 
                                        class="block w-full text-sm text-gray-400
                                        file:mr-4 file:py-3 file:px-6
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-bold
                                        file:bg-gray-800 file:text-yellow-400
                                        group-hover:file:bg-yellow-400 group-hover:file:text-black
                                        file:transition file:cursor-pointer cursor-pointer border border-gray-700 rounded-xl bg-gray-900/50"
                                    >
                                </div>
                                <div v-if="form.errors.video_file" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.video_file }}</div>
                                
                                <div v-if="form.progress" class="w-full bg-gray-800 rounded-full h-2.5 mt-4 overflow-hidden">
                                    <div class="bg-yellow-400 h-2.5 rounded-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-bold text-gray-400">Transcripción (JSON)</label>
                                    <span class="text-xs text-gray-500 bg-gray-800 px-2 py-1 rounded">Solo pega el texto crudo</span>
                                </div>
                                <textarea 
                                    v-model="form.transcript_json" 
                                    rows="6" 
                                    class="w-full bg-gray-900 border border-gray-700 rounded-xl text-gray-300 font-mono text-xs px-4 py-3 focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition leading-relaxed" 
                                    placeholder='[{"start": 0, "end": 2, "text": "Hello world"}]'
                                ></textarea>
                                <div v-if="form.errors.transcript_json" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.transcript_json }}</div>
                            </div>
                        </div>

                        <div class="space-y-6 pt-4">
                            <h3 class="text-lg font-bold text-white border-b border-gray-700 pb-2">2. El Desafío (Quiz)</h3>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-400 mb-2">Pregunta</label>
                                <input 
                                    v-model="form.question_text" 
                                    type="text" 
                                    class="w-full bg-gray-900 border border-gray-700 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                                    placeholder="¿Qué significa...?"
                                >
                            </div>

                            <div class="space-y-4">
                                <div class="relative">
                                    <label class="block text-xs font-bold text-green-400 mb-1 uppercase tracking-wider">Respuesta Correcta</label>
                                    <input v-model="form.correct_option" type="text" class="w-full bg-gray-900 border-2 border-green-500/20 rounded-xl text-white px-4 py-3 focus:border-green-500 transition focus:ring-0">
                                    <div class="absolute top-9 right-3 text-green-500">✓</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-red-400 mb-1 uppercase tracking-wider">Falsa 1</label>
                                        <input v-model="form.wrong_option_1" type="text" class="w-full bg-gray-900 border border-red-500/20 rounded-xl text-gray-400 px-4 py-3 focus:border-red-500 transition focus:ring-0 focus:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-red-400 mb-1 uppercase tracking-wider">Falsa 2</label>
                                        <input v-model="form.wrong_option_2" type="text" class="w-full bg-gray-900 border border-red-500/20 rounded-xl text-gray-400 px-4 py-3 focus:border-red-500 transition focus:ring-0 focus:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6 border-t border-gray-800">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full sm:w-auto bg-yellow-400 hover:bg-yellow-300 text-black font-black uppercase tracking-widest py-4 px-10 rounded-full shadow-[0_0_20px_rgba(250,204,21,0.4)] transform transition hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                            >
                                <span v-if="form.processing" class="animate-spin">⏳</span>
                                {{ form.processing ? 'Subiendo...' : 'Publicar Video' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>