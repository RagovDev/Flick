import { ref, watch } from 'vue';

// 1. Leemos la memoria del navegador. Si no hay nada, por defecto es 'false' (con sonido).
// Al estar declarada FUERA de la función, esta variable es GLOBAL para toda la app.
const savedState = localStorage.getItem('flick_is_muted');
const isGlobalMuted = ref(savedState ? JSON.parse(savedState) : false);

// 2. Cada vez que cambie, la guardamos automáticamente en el navegador.
watch(isGlobalMuted, (newVal) => {
    localStorage.setItem('flick_is_muted', JSON.stringify(newVal));
});

export function useAudio() {
    return { isGlobalMuted };
}