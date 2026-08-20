<x-filament-panels::page>
    <div class="mx-auto max-w-6xl space-y-10">
        <header class="rounded-2xl border border-primary-200 bg-primary-50 p-6 dark:border-primary-500/30 dark:bg-primary-500/10">
            <p class="text-sm font-semibold uppercase tracking-wide text-primary-700 dark:text-primary-300">Guía de uso</p>
            <h2 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">KDP Author Manager</h2>
            <p class="mt-3 max-w-3xl text-gray-700 dark:text-gray-200">Esta guía explica qué hace cada pantalla, qué relaciones mantiene la aplicación y cómo interpretar los datos procedentes de Amazon KDP.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a class="fi-btn fi-btn-size-md inline-flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500" href="{{ asset('manual/manual_lmsgi_kdp_corregido.html') }}" target="_blank" rel="noopener noreferrer">Abrir manual LMSGI completo</a>
                <a class="fi-btn fi-btn-size-md inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-white/5" href="#indice">Ir al índice</a>
            </div>
        </header>

        <nav id="indice" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900" aria-label="Índice de la guía">
            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Índice</h3>
            <div class="mt-4 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#inicio">1. Inicio y navegación</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#obras">2. Obras y catálogo editorial</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#publicaciones">3. Publicaciones</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#manuscritos">4. Manuscritos y versiones</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#kdp">5. Informes KDP y regalías</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#tareas">6. Tareas y checklists</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#marketing">7. Promociones y costes</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#catalogos">8. Catálogos configurables</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#eventos">9. Eventos, fuentes e IA</a>
                <a class="text-primary-700 hover:underline dark:text-primary-300" href="#problemas">10. Problemas frecuentes</a>
            </div>
        </nav>

        <section id="inicio" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">1. Inicio y navegación</h3>
            <p>La pantalla Inicio resume el catálogo y la actividad reciente. Los indicadores respetan el usuario autenticado y separan las monedas de las regalías.</p>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/01-inicio.png') }}" alt="Pantalla de inicio con indicadores, tareas y datos KDP" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Inicio: obras, regalías del periodo, tareas, publicaciones, promociones y rendimiento KDP.</figcaption></figure>
            <ul class="list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-gray-300">
                <li><strong>Obras:</strong> abre el catálogo editorial.</li>
                <li><strong>Regalías del periodo:</strong> muestra el importe disponible sin sumar monedas distintas. Si el mes actual no tiene datos, usa el último periodo KDP importado.</li>
                <li><strong>Próximas tareas:</strong> ordena trabajo pendiente por fecha y prioridad.</li>
                <li><strong>Rendimiento KDP:</strong> resume unidades, KENP, monedas, obras y regalías importadas.</li>
            </ul>
        </section>

        <section id="obras" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">2. Obras y catálogo editorial</h3>
            <p>Una obra agrupa títulos, autor, idiomas, géneros, manuscritos, publicaciones, tareas y checklists. Puede tener varias publicaciones: eBook, tapa blanda y audiolibro.</p>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/02-obras.png') }}" alt="Listado de obras" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Listado de obras con búsqueda, filtros, ordenación y acciones de detalle/edición.</figcaption></figure>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/08-nueva-obra.png') }}" alt="Formulario de nueva obra" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Alta de obra: datos básicos, clasificación, idioma, estado, notas y fechas.</figcaption></figure>
            <p><strong>Campos que suelen confundirse:</strong> el título interno es para el equipo; el título público aparece en catálogo; el slug es el identificador corto y único usado en URLs; el estado representa la fase editorial, no el estado de una publicación concreta.</p>
            <p><strong>Relaciones:</strong> una obra pertenece a un usuario; tiene muchos idiomas de obra, manuscritos, publicaciones, tareas, fuentes y checklists. Puede relacionarse con hasta tres géneros y subgéneros.</p>
        </section>

        <section id="publicaciones" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">3. Publicaciones</h3>
            <p>Una publicación representa una edición concreta distribuida en una plataforma y marketplace. Se seleccionan obra, idioma, manuscrito final, plataforma, marketplace, formato, estado, precio, ISBN y ASIN.</p>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/03-publicaciones.png') }}" alt="Listado de publicaciones" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Publicaciones: cada fila es una edición y destino comercial concretos.</figcaption></figure>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/09-nueva-publicacion.png') }}" alt="Formulario de nueva publicación" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Alta de publicación mediante pasos: obra, idioma, manuscrito, plataforma y metadatos.</figcaption></figure>
            <p>La aplicación comprueba que obra, idioma, manuscrito y marketplace son compatibles. Desde una publicación se gestionan metadatos KDP, historial de precios y observaciones de mercado.</p>
        </section>

        <section id="manuscritos" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">4. Manuscritos y versiones</h3>
            <p>Una versión de manuscrito conserva el historial del contenido. Elige la obra y después un idioma configurado para esa obra; la publicación debe utilizar una versión final compatible.</p>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/10-nueva-version.png') }}" alt="Formulario de nueva versión de manuscrito" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Versión de manuscrito: idioma, versión padre, contenido, archivo y estados editoriales.</figcaption></figure>
            <p>El campo Archivo permite seleccionar un fichero del equipo. Las estadísticas de palabras, capítulos e imágenes se calculan a partir del contenido. Marcar una versión como final no equivale a publicarla.</p>
        </section>

        <section id="kdp" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">5. Informes KDP y regalías</h3>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/12-nueva-importacion.png') }}" alt="Formulario para cargar informes KDP" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Carga de CSV, XLSX o ZIP; el sistema detecta tipo y periodo por archivo.</figcaption></figure>
            <p>Los informes se guardan en sesiones y lotes. El SHA-256 evita duplicados. Las filas se clasifican como regalía, pedido, KENP, preventa, estimación o pago.</p>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/04-desglose-kdp.png') }}" alt="Desglose de informes KDP" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Desglose KDP: búsqueda, filtros, columnas ordenables y acceso a la publicación relacionada.</figcaption></figure>
            <p><strong>Regalías acumuladas por obra:</strong> el panel agrupa `total_earnings` por obra y moneda. Las filas vinculadas usan la obra de la publicación; las no vinculadas muestran su título de origen. Nunca se mezclan monedas sin tipo de cambio documentado.</p>
            <p><strong>Reprocesar:</strong> vuelve a leer el archivo original, reemplaza sus filas derivadas y recalcula catálogo, pagos y errores. Si el archivo falta o ha cambiado, conserva los datos anteriores y muestra el error del lote.</p>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/13-pagos-kdp.png') }}" alt="Listado de pagos KDP" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Pagos KDP: importes, retenciones, moneda, estado y archivo de procedencia.</figcaption></figure>
            <p>El Payment Report de KDP no suele incluir ASIN ni obra. La aplicación puede conciliarlo con regalías definitivas por periodo, marketplace y moneda, pero debe marcar como calculada cualquier asignación indirecta.</p>
        </section>

        <section id="tareas" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">6. Tareas y checklists</h3>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/06-tareas.png') }}" alt="Listado de tareas" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Tareas pendientes, asignación, prioridad, estado y vencimiento.</figcaption></figure>
            <figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"><img class="w-full" src="{{ asset('manual/screenshots/11-nueva-tarea.png') }}" alt="Formulario de nueva tarea" loading="lazy"><figcaption class="p-3 text-sm text-gray-500">Una tarea puede ser general de una obra o específica de una publicación.</figcaption></figure>
            <p>Los tipos de tarea se administran desde <strong>Tipos de tarea</strong>. Una checklist pertenece a una obra y sus elementos se gestionan desde su relación. Son útiles para preflight de publicación, revisión, traducción y lanzamiento.</p>
        </section>

        <section id="marketing" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">7. Promociones y costes</h3>
            <p>Una promoción pertenece a una publicación y puede tener costes y resultados diarios. Los costes son inserciones manuales: publicidad, promoción, herramientas, marketing u otros. No deben confundirse con regalías KDP.</p>
            <p>La aplicación calcula ventas, ingresos, costes y ROI de la campaña, además de detectar promociones activas solapadas. El resultado es analítico y conserva moneda, periodo y fuente.</p>
        </section>

        <section id="catalogos" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">8. Catálogos configurables</h3>
            <p><strong>Idiomas</strong> alimenta los desplegables de obra y de idiomas de obra. <strong>Géneros y subgéneros</strong> organizan la clasificación editorial. <strong>Tipos de tarea</strong> permiten adaptar el flujo de trabajo.</p>
            <p>Los catálogos deben mantenerse activos sólo cuando puedan seleccionarse en nuevos formularios. Desactivar un valor conserva el histórico, pero evita nuevas asignaciones.</p>
        </section>

        <section id="eventos" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">9. Eventos, fuentes e inteligencia artificial</h3>
            <p><strong>Eventos:</strong> calendario de ferias, firmas, presentaciones y conferencias con fecha, lugar, organizador y estado.</p>
            <p><strong>Fuentes y usos:</strong> bibliografía asociada a una obra y su utilización en versiones o capítulos, con verificación.</p>
            <p><strong>Prompts, herramientas y tareas IA:</strong> registran apoyo de inteligencia artificial y resultados revisables. La IA no sustituye la revisión humana ni debe inventar datos editoriales.</p>
        </section>

        <section id="problemas" class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-950 dark:text-white">10. Problemas frecuentes</h3>
            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-white/10">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5"><tr><th class="px-4 py-3 font-semibold">Síntoma</th><th class="px-4 py-3 font-semibold">Qué comprobar</th></tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        <tr><td class="px-4 py-3">No aparecen regalías</td><td class="px-4 py-3">Revisar fila de tipo regalía, moneda, importe, periodo y filtros del desglose.</td></tr>
                        <tr><td class="px-4 py-3">Reprocesado sin filas</td><td class="px-4 py-3">Abrir el lote y revisar si el archivo original existe, coincide su huella y contiene una cabecera reconocible.</td></tr>
                        <tr><td class="px-4 py-3">El idioma no aparece</td><td class="px-4 py-3">Activar el idioma en el catálogo y crear primero la relación en la pestaña Idiomas de la obra.</td></tr>
                        <tr><td class="px-4 py-3">No se puede publicar</td><td class="px-4 py-3">Comprobar obra, idioma, manuscrito final, plataforma, marketplace y ASIN.</td></tr>
                        <tr><td class="px-4 py-3">Una tabla no ordena</td><td class="px-4 py-3">Pulsar la cabecera; las columnas calculadas sólo ordenan si tienen una consulta SQL explícita.</td></tr>
                        <tr><td class="px-4 py-3">No puedo ver un registro</td><td class="px-4 py-3">Comprobar usuario propietario y rol. Los autores no pueden acceder a datos ajenos.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">Para diagnóstico técnico se pueden ejecutar <code>php artisan app:health</code> y <code>php artisan test</code>. El manual LMSGI enlazado arriba explica además los formatos HTML, XML, JSON, CSV y Markdown utilizados en los procesos.</p>
        </section>
    </div>
</x-filament-panels::page>
