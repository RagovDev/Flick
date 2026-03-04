<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { UploadCloud, Film, Loader2 } from 'lucide-vue-next';

// El formulario ahora es súper limpio. Solo 3 cosas.
const form = useForm({
    title: '',
    category: 'movies', // Valor por defecto
    video_file: null,
});

const submit = () => {
    form.post(route('admin.clips.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Subir Video con IA" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-white leading-tight flex items-center gap-2">
                    <UploadCloud class="text-yellow-400" />
                    Subir Nuevo Video
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-gray-800 border border-gray-700 overflow-hidden shadow-2xl sm:rounded-2xl p-8">
                    
                    <div class="mb-8 border-b border-gray-700 pb-6">
                        <h3 class="text-xl font-bold text-white mb-2">Asistente de Inteligencia Artificial</h3>
                        <p class="text-gray-400 text-sm">
                            Sube un clip de 30 segundos. Nuestro sistema extraerá los subtítulos automáticamente 
                            y Gemini IA generará un Quiz interactivo basado en el diálogo.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Título del Video</label>
                            <input 
                                id="title" 
                                v-model="form.title" 
                                type="text" 
                                class="w-full bg-gray-900 border border-gray-600 rounded-xl shadow-sm px-4 py-3 text-white focus:ring-yellow-500 focus:border-yellow-500 transition" 
                                placeholder="Ej: Escena épica de Batman"
                                required
                            >
                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Canal / Categoría</label>
                            <select 
                                id="category" 
                                v-model="form.category" 
                                class="w-full bg-gray-900 border border-gray-600 rounded-xl shadow-sm px-4 py-3 text-white focus:ring-yellow-500 focus:border-yellow-500 transition appearance-none"
                                required
                            >
                                <option value="movies">Cine & TV 🎬</option>
                                <option value="music">Música 🎵</option>
                                <option value="tech">Tech & IA 💻</option>
                                <option value="travel">Viajes ✈️</option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-500 text-xs mt-1">{{ form.errors.category }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Archivo de Video (Max 50MB)</label>
                            
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-xl hover:border-yellow-500 transition bg-gray-900/50 group relative">
                                <div class="space-y-2 text-center">
                                    <Film class="mx-auto h-12 w-12 text-gray-500 group-hover:text-yellow-400 transition" />
                                    <div class="flex text-sm text-gray-400 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer rounded-md font-bold text-yellow-500 hover:text-yellow-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-yellow-500">
                                            <span>Sube un archivo .mp4</span>
                                            <input 
                                                id="file-upload" 
                                                type="file" 
                                                class="sr-only" 
                                                accept="video/mp4,video/quicktime"
                                                @input="form.video_file = $event.target.files[0]"
                                                required
                                            >
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ form.video_file ? form.video_file.name : 'O arrástralo y suéltalo aquí' }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="form.errors.video_file" class="text-red-500 text-xs mt-1">{{ form.errors.video_file }}</div>
                        </div>

                        <div class="pt-4 flex items-center justify-end">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-yellow-500 hover:bg-yellow-400 text-black font-black py-3 px-8 rounded-xl shadow-[0_0_15px_rgba(250,204,21,0.3)] transition transform hover:-translate-y-1 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                            >
                                <template v-if="form.processing">
                                    <Loader2 class="animate-spin" size="20" />
                                    <span>Analizando con IA... (Tardará un poco)</span>
                                </template>
                                <template v-else>
                                    <UploadCloud size="20" />
                                    <span>Subir y Generar Quiz</span>
                                </template>
                            </button>
                        </div>
                        
                    </form>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>