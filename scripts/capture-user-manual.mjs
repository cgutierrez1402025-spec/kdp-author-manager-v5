import { spawn } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';

const baseUrl = process.env.MANUAL_BASE_URL ?? 'http://127.0.0.1:8030';
const chromePath = process.env.CHROME_PATH ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const outputDirectory = new URL('../docs/images/manual/', import.meta.url);
const debuggingPort = 9333;

const allPages = [
    ['inicio', '/admin'],
    ['obras', '/admin/works'],
    ['obra-nueva', '/admin/works/create'],
    ['manuscritos', '/admin/manuscript-versions'],
    ['tareas', '/admin/tasks'],
    ['tipos-tarea', '/admin/task-types'],
    ['checklists', '/admin/checklists'],
    ['publicaciones', '/admin/publications'],
    ['audiolibros', '/admin/audiolibros'],
    ['narradores', '/admin/narradores'],
    ['importar-kdp', '/admin/importaciones-kdp/create'],
    ['sesiones-importacion', '/admin/sesiones-importacion-kdp'],
    ['catalogo-kdp', '/admin/catalogo-detectado-kdp'],
    ['desglose-kdp', '/admin/desglose-informes-kdp'],
    ['pagos-kdp', '/admin/pagos-kdp'],
    ['metadatos-kdp', '/admin/kdp-metadata'],
    ['periodos-kdp-select', '/admin/kdp-select-periods'],
    ['promociones', '/admin/book-promotions'],
    ['costes-promocion', '/admin/promotion-costs'],
    ['resultados-promocion', '/admin/promotion-daily-results'],
    ['eventos', '/admin/book-events'],
    ['libros-eventos', '/admin/event-books'],
    ['fuentes', '/admin/sources'],
    ['usos-fuente', '/admin/source-usages'],
    ['prompts', '/admin/prompts'],
    ['tareas-ia', '/admin/ai-tasks'],
    ['anclajes-ilustraciones', '/admin/illustration-anchors'],
    ['idiomas', '/admin/languages'],
    ['plataformas', '/admin/platforms'],
    ['marketplaces', '/admin/marketplaces'],
    ['ayuda', '/admin/help'],
];
const requestedPage = process.env.MANUAL_PAGE;
const pages = requestedPage ? allPages.filter(([name]) => name === requestedPage) : allPages;

await mkdir(outputDirectory, { recursive: true });

const chrome = spawn(chromePath, [
    '--headless=new',
    '--disable-gpu',
    '--no-first-run',
    '--no-default-browser-check',
    '--hide-scrollbars',
    `--remote-debugging-port=${debuggingPort}`,
    '--window-size=1600,1100',
    `--user-data-dir=/private/tmp/kdp-manual-chrome-${process.pid}`,
    'about:blank',
], { stdio: 'ignore' });

const delay = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));

async function debuggerTarget() {
    for (let attempt = 0; attempt < 50; attempt++) {
        try {
            const targets = await fetch(`http://127.0.0.1:${debuggingPort}/json`).then((response) => response.json());
            const page = targets.find((target) => target.type === 'page');
            if (page) return page;
        } catch {}
        await delay(200);
    }
    throw new Error('Chrome no inició el puerto de depuración.');
}

const target = await debuggerTarget();
const socket = new WebSocket(target.webSocketDebuggerUrl);
const pending = new Map();
let nextId = 1;

socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    if (!message.id || !pending.has(message.id)) return;
    const { resolve, reject } = pending.get(message.id);
    pending.delete(message.id);
    message.error ? reject(new Error(message.error.message)) : resolve(message.result);
});

await new Promise((resolve, reject) => {
    socket.addEventListener('open', resolve, { once: true });
    socket.addEventListener('error', reject, { once: true });
});

function command(method, params = {}) {
    const id = nextId++;
    socket.send(JSON.stringify({ id, method, params }));
    return new Promise((resolve, reject) => pending.set(id, { resolve, reject }));
}

async function navigate(path) {
    await command('Page.navigate', { url: `${baseUrl}${path}` });
    await delay(1300);
}

try {
    await command('Page.enable');
    await command('Runtime.enable');
    await command('Emulation.setDeviceMetricsOverride', {
        width: 1600,
        height: 1100,
        deviceScaleFactor: 1,
        mobile: false,
    });

    await navigate('/admin/login');
    await command('Runtime.evaluate', {
        expression: `(() => {
            const email = document.querySelector('input[type="email"]');
            const password = document.querySelector('input[type="password"]');
            if (!email || !password) throw new Error('No se encontró el formulario de acceso');
            email.value = 'admin@kdpmanager.local';
            email.dispatchEvent(new Event('input', { bubbles: true }));
            password.value = 'password';
            password.dispatchEvent(new Event('input', { bubbles: true }));
            document.querySelector('form').requestSubmit();
        })()`,
    });
    await delay(1800);

    for (const [name, path] of pages) {
        await navigate(path);
        const title = await command('Runtime.evaluate', { expression: 'document.title', returnByValue: true });
        const screenshot = await command('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
            fromSurface: true,
        });
        await writeFile(new URL(`${name}.png`, outputDirectory), Buffer.from(screenshot.data, 'base64'));
        process.stdout.write(`${name}: ${title.result.value}\n`);
    }
} finally {
    socket.close();
    chrome.kill('SIGTERM');
}
