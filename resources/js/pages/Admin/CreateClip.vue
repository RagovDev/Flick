<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { UploadCloud, Film, Loader2, AlertTriangle, CheckCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage();

const form = useForm({
    title: '',
    category: 'movies',
    video_file: null,
});

const isDragging = ref(false);

// 🌟 MAGIA 1: Funciones reales de Drag & Drop
const handleDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file && (file.type === 'video/mp4' || file.type === 'video/quicktime')) {
        form.video_file = file;
    } else {
        alert('Por favor, sube solo archivos .mp4 o .mov');
    }
};

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
                            Sube un clip de 60 segundos. Nuestro sistema extraerá los subtítulos automáticamente 
                            y Gemini IA generará un Quiz interactivo basado en el diálogo.
                        </p>
                    </div>

                    <div v-if="$page.props.errors.error" class="mb-6 bg-red-500/10 border border-red-500/50 p-4 rounded-xl flex items-start gap-3">
                        <AlertTriangle class="text-red-500 shrink-0 mt-0.5" size="20" />
                        <div>
                            <h4 class="text-red-400 font-bold text-sm">Error en el procesamiento</h4>
                            <p class="text-gray-300 text-sm mt-1">{{ $page.props.errors.error }}</p>
                            <p v-if="$page.props.errors.debug" class="text-xs text-red-500/70 mt-2 font-mono bg-black/20 p-2 rounded">{{ $page.props.errors.debug }}</p>
                        </div>
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
                            
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition group relative"
                                :class="isDragging ? 'border-yellow-400 bg-yellow-400/10' : 'border-gray-600 hover:border-yellow-500 bg-gray-900/50'"
                            >
                                <div class="space-y-2 text-center pointer-events-none">
                                    <div v-if="form.video_file" class="flex flex-col items-center">
                                        <CheckCircle class="mx-auto h-12 w-12 text-green-400 mb-2" />
                                        <span class="text-sm font-bold text-yellow-400">{{ form.video_file.name }}</span>
                                        <span class="text-xs text-gray-500">{{ (form.video_file.size / 1024 / 1024).toFixed(2) }} MB</span>
                                    </div>
                                    <div v-else>
                                        <Film class="mx-auto h-12 w-12 text-gray-500 transition" :class="{ 'text-yellow-400 scale-110': isDragging }" />
                                        <div class="flex text-sm text-gray-400 justify-center mt-2 pointer-events-auto">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md font-bold text-yellow-500 hover:text-yellow-400">
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
                                        <p class="text-xs text-gray-500 mt-1">
                                            O arrástralo y suéltalo aquí
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.video_file" class="text-red-500 text-xs mt-1">{{ form.errors.video_file }}</div>

                            <div v-if="form.progress" class="mt-4">
                                <div class="flex justify-between text-xs text-gray-400 mb-1 font-mono">
                                    <span>Subiendo archivo al servidor...</span>
                                    <span>{{ form.progress.percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-yellow-400 h-1.5 rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(250,204,21,0.5)]" :style="{ width: form.progress.percentage + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-yellow-500 hover:bg-yellow-400 text-black font-black py-3 px-8 rounded-xl shadow-[0_0_15px_rgba(250,204,21,0.3)] transition transform flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{ 'hover:-translate-y-1': !form.processing }"
                            >
                                <template v-if="form.processing">
                                    <Loader2 class="animate-spin" size="20" />
                                    <span>{{ form.progress && form.progress.percentage === 100 ? 'Analizando con IA...' : 'Subiendo video...' }}</span>
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