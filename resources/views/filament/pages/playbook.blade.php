<x-filament-panels::page>
    <div class="space-y-6" style="max-width: 60rem;">

        <p class="text-sm text-gray-500 dark:text-gray-400">
            No vendemos un discurso, abrimos una conversación. Preguntamos, escuchamos y la demo solo entra cuando hay interés real. Nunca prometemos el nº1 en Google.
        </p>

        {{-- 1. Mejores leads --}}
        <x-filament::section>
            <x-slot name="heading">1 · Quiénes son tus mejores leads</x-slot>
            <x-slot name="description">El CRM ya los puntúa solo. En la tabla de Leads usa el filtro «Prioridad alta (70+)».</x-slot>

            <div class="space-y-3 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-3">Sin página web <span class="font-semibold text-primary-600">+40</span> · la mayor oportunidad</div>
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-3">Pocas o ninguna reseña <span class="font-semibold text-primary-600">+25</span> · ficha descuidada</div>
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-3">Buena valoración 4★+ <span class="font-semibold text-primary-600">+15</span> · negocio que funciona</div>
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-3">Móvil / WhatsApp <span class="font-semibold text-primary-600">+20</span> · fácil de contactar</div>
                </div>
                <p><span class="font-semibold">Señal de oro:</span> negocio que va bien pero con mala presencia digital (sin web, ficha abandonada). Le duele y puede pagar.</p>
                <p><span class="font-semibold">Sector que encaja:</span> peluquerías, estética, uñas, depilación, barberías, centros de belleza.</p>
                <p><span class="font-semibold text-danger-600">Despriorizar:</span> cadenas/franquicias, sin teléfono o solo fijo, valoración muy baja y sin movimiento, web reciente y cuidada.</p>
            </div>
        </x-filament::section>

        {{-- 2. Orden del día --}}
        <x-filament::section>
            <x-slot name="heading">2 · Orden de trabajo del día</x-slot>
            <div class="overflow-x-auto text-sm">
                <table class="w-full text-left">
                    <thead class="text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10">
                        <tr><th class="py-2 pr-4">Situación del lead</th><th class="py-2 pr-4">Acción</th><th class="py-2">Canal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <tr><td class="py-2 pr-4">Prioridad 70+ y móvil</td><td class="py-2 pr-4 font-medium">Contactar hoy</td><td class="py-2">WhatsApp o llamada</td></tr>
                        <tr><td class="py-2 pr-4">Prioridad 45–69</td><td class="py-2 pr-4">Esta semana</td><td class="py-2">WhatsApp</td></tr>
                        <tr><td class="py-2 pr-4">Sin web + buena valoración</td><td class="py-2 pr-4 font-medium">Prioridad máxima</td><td class="py-2">Llamada</td></tr>
                        <tr><td class="py-2 pr-4">Solo fijo</td><td class="py-2 pr-4">Bloque aparte</td><td class="py-2">Llamada</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Cada uno trabaja su lista de «asignados» de mayor a menor prioridad. Tras cada contacto, registra la actividad y pon la próxima fecha de seguimiento. Si no está en el CRM, no existe.</p>
        </x-filament::section>

        {{-- 3. Guiones --}}
        <x-filament::section>
            <x-slot name="heading">3 · Qué decirles</x-slot>
            <x-slot name="description">Plantillas para adaptar, no para leer de corrido. Tono cercano, de tú, sin tecnicismos.</x-slot>

            <div class="space-y-5">
                {{-- WhatsApp --}}
                <div x-data="{ copied: false }" class="rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-sm">Primer mensaje de WhatsApp</span>
                        <button type="button"
                            x-on:click="navigator.clipboard.writeText($refs.wa.innerText); copied=true; setTimeout(()=>copied=false,1500)"
                            class="text-xs rounded-lg px-2.5 py-1 bg-primary-600 text-white hover:bg-primary-500"
                            x-text="copied ? 'Copiado ✓' : 'Copiar'"></button>
                    </div>
                    <div x-ref="wa" class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">Hola, ¿hablo con [negocio]? Soy [tu nombre], de CercaDigital 👋
Os he encontrado en Google y me ha llamado la atención vuestro sitio. Ayudo a negocios como el vuestro a que os encuentren mejor por la zona. ¿Tenéis un momento esta semana para que os enseñe una idea concreta para [negocio]?</div>
                </div>

                {{-- Llamada --}}
                <div x-data="{ copied: false }" class="rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-sm">Guion de llamada</span>
                        <button type="button"
                            x-on:click="navigator.clipboard.writeText($refs.call.innerText); copied=true; setTimeout(()=>copied=false,1500)"
                            class="text-xs rounded-lg px-2.5 py-1 bg-primary-600 text-white hover:bg-primary-500"
                            x-text="copied ? 'Copiado ✓' : 'Copiar'"></button>
                    </div>
                    <div x-ref="call" class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">APERTURA (honesta, 10 seg):
Hola, ¿[negocio]? Soy [tu nombre], de CercaDigital. No os quiero robar tiempo: os he visto en Google y os llamo porque he detectado un par de cosas de vuestra presencia online que se podrían mejorar fácil. ¿Os pillo bien 2 minutos?

PREGUNTAS (escucha, no vendas):
· ¿Cómo os suelen encontrar los clientes nuevos, más por Google, Instagram, boca a boca…?
· ¿Tenéis web o vais más con la ficha de Google y redes?
· Cuando alguien os busca en el móvil, ¿os es fácil que os llamen o pidan cita?

GANCHO:
Os puedo preparar sin compromiso una demostración de cómo se vería vuestro negocio mejor presentado en Google y con una web sencilla. Si os encaja, seguimos; si no, no pasa nada. ¿Os la enseño?

CIERRE A DEMO (no a venta):
Genial, dame un día y te paso algo hecho para [negocio]. ¿Te va mejor por WhatsApp o en una llamada rápida?</div>
                </div>

                {{-- Variantes --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4 text-sm space-y-2">
                    <span class="font-semibold">Variantes según su ficha</span>
                    <p><span class="font-medium">Sin web:</span> «He visto que no tenéis web. Hoy la gente antes de ir mira en el móvil, y si no encuentran nada, tiran al de al lado. Eso se arregla fácil.»</p>
                    <p><span class="font-medium">Ficha descuidada:</span> «Vuestra ficha de Google está a medias, sin fotos y con datos incompletos. Es lo primero que ve un cliente nuevo.»</p>
                    <p><span class="font-medium">Pocas reseñas:</span> «Se ve que trabajáis bien, pero tenéis muy pocas reseñas para el movimiento que lleváis. Con un sistema sencillo para pedirlas se nota un montón.»</p>
                </div>

                {{-- Objeciones --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4 text-sm space-y-2">
                    <span class="font-semibold">Objeciones frecuentes</span>
                    <p><span class="font-medium">«No me interesa»:</span> «Me alegro de que os vaya bien. Justo por eso: no es captar a lo loco, es no perder a los que ya os buscan y no os encuentran. Os dejo la demo y decidís, sin compromiso.»</p>
                    <p><span class="font-medium">«¿Cuánto cuesta?»:</span> «Cuotas mensuales desde 99 €, según lo que necesitéis. Pero antes de precio prefiero enseñaros la demo, así veis qué recibís.»</p>
                    <p><span class="font-medium">«Ya me lo lleva un familiar»:</span> «Genial tener a alguien. Lo nuestro se mantiene cada mes con resultados e informe, no es hacerlo una vez y olvidarse. Os enseño la diferencia y comparáis.»</p>
                    <p><span class="font-medium">«Mándame información»:</span> «Claro, pero mejor os preparo la demo con vuestro propio negocio, se entiende mucho mejor. ¿Os la paso mañana?»</p>
                </div>
            </div>
        </x-filament::section>

        {{-- 4. Seguimiento --}}
        <x-filament::section>
            <x-slot name="heading">4 · Seguimiento (que no se caiga nadie)</x-slot>
            <x-slot name="description">La mayoría de ventas llegan en el 2º–4º contacto, no en el primero.</x-slot>

            <div class="overflow-x-auto text-sm">
                <table class="w-full text-left">
                    <thead class="text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10">
                        <tr><th class="py-2 pr-4">Momento</th><th class="py-2">Acción</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <tr><td class="py-2 pr-4 font-medium">Día 0</td><td class="py-2">Primer contacto (WhatsApp o llamada)</td></tr>
                        <tr><td class="py-2 pr-4 font-medium">Día +2</td><td class="py-2">Si no responde: recordatorio corto</td></tr>
                        <tr><td class="py-2 pr-4 font-medium">Día +5</td><td class="py-2">Llamada o nota de voz</td></tr>
                        <tr><td class="py-2 pr-4 font-medium">Día +10</td><td class="py-2">Último toque con la demo hecha</td></tr>
                        <tr><td class="py-2 pr-4 font-medium">Sin respuesta</td><td class="py-2">Marcar «Cerrado perdido» y reintentar en 2–3 meses</td></tr>
                    </tbody>
                </table>
            </div>

            <div x-data="{ copied: false }" class="mt-4 rounded-xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-sm">Recordatorio (día +2)</span>
                    <button type="button"
                        x-on:click="navigator.clipboard.writeText($refs.rem.innerText); copied=true; setTimeout(()=>copied=false,1500)"
                        class="text-xs rounded-lg px-2.5 py-1 bg-primary-600 text-white hover:bg-primary-500"
                        x-text="copied ? 'Copiado ✓' : 'Copiar'"></button>
                </div>
                <div x-ref="rem" class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">Hola [nombre], ¿pudiste verlo? Sin prisa, solo por si se te traspapeló. Te dejo aquí la idea que te comentaba para [negocio] 🙂</div>
            </div>

            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Registra cada toque como Actividad en la ficha y rellena «Seguimiento programado» con la próxima fecha: aparece en el panel de Próximos seguimientos.</p>
        </x-filament::section>

        {{-- 5. Nota legal --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">5 · Nota de prudencia (España)</x-slot>
            <div class="text-sm space-y-2 text-gray-600 dark:text-gray-300">
                <p>Contactas negocios con datos públicos de Google, razonable para una oferta B2B. Aun así:</p>
                <p>· En <span class="font-medium">llamadas</span>, respeta la Lista Robinson; si piden no volver a llamar, se anota y se respeta.</p>
                <p>· En <span class="font-medium">WhatsApp/email en frío</span>, identifícate siempre y ofrece parar el contacto. Nada de envíos masivos automáticos sin control.</p>
                <p>· Si montas emailing o formularios que recojan datos, revísalo con un asesor (política de privacidad y baja). Esto es orientación, no asesoría legal.</p>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
