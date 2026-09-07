# 🎬 Flick - Aprende Inglés con el Poder del "Scroll Productivo"

**Flick** es una plataforma web innovadora diseñada para revolucionar el aprendizaje del inglés. Combina la interfaz adictiva del scroll infinito (estilo TikTok/Reels) con **Active Recall** (quizzes en contexto) y estrategias avanzadas de **Gamificación y Diseño de Comportamiento** (Modelo Hook).

En lugar de perder tiempo en redes sociales, Flick convierte el scroll en un hábito educativo altamente recompensado.

---

## ✨ Características Principales

### 📱 Experiencia "Scroll Infinito"
- **Reproductor Inmersivo:** Interfaz optimizada para móviles y escritorio usando Vue 3 y Tailwind CSS.
- **Carga Predictiva (Buffer Agresivo):** El sistema pre-carga silenciosamente los siguientes videos en la fila para garantizar transiciones instantáneas y cero fricción, manteniendo la inmersión del usuario.

### 🧠 Aprendizaje Interactivo
- **Quizzes Integrados:** Los videos se pausan en momentos clave para evaluar la comprensión auditiva y el vocabulario en contexto real.
- **Modo Práctica / Repaso:** Algoritmos que permiten al usuario repasar videos fallados o completados previamente.

### 🎮 Gamificación y Psicología de Producto
- **🔥 Sistema de Rachas (Streaks):** Diseñado bajo el principio de "Aversión a la Pérdida". Los usuarios deben completar al menos un quiz diario para mantener vivo su fuego.
- **🎰 Recompensas Variables:** Efecto tragamonedas implementado en el backend. Los aciertos tienen porcentajes aleatorios de convertirse en *Golpes Críticos* o *Jackpots* de XP, generando picos de dopamina.
- **🏆 Curva de Experiencia (XP):** Progresión dinámica de niveles calculada en tiempo real. Los usuarios avanzan desde *Novato del Inglés 🌱* hasta *Leyenda Flick 👑*.
- **📊 Centro de Mando:** Dashboard reactivo que muestra estadísticas, precisión, rachas y progreso visual sin recargar la página.

---

## 🛠️ Stack Tecnológico

**Backend (La fuente de la verdad):**
- [Laravel](https://laravel.com/) (PHP) - Lógica de negocio, seguridad anti-trampas y cálculos de gamificación.
- **Base de Datos:** MySQL / MariaDB (Manejo de estados, progreso de usuarios y control de rachas con *Carbon*).

**Frontend (Reactividad y Magia):**
- [Vue.js 3](https://vuejs.org/) (Composition API) - Componentes modulares e interfaces fluidas.
- [Inertia.js](https://inertiajs.com/) - El puente perfecto que une Laravel y Vue en una SPA (Single Page Application) sin construir una API REST clásica.
- [Tailwind CSS](https://tailwindcss.com/) - Diseño responsivo, animaciones fluidas y UI moderna.
- **Librerías Extra:** `axios` (Peticiones asíncronas), `canvas-confetti` (Efectos visuales de victoria), `lucide-vue-next` (Iconografía limpia).

