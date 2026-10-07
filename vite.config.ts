import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig, lazyPlugins } from 'vite-plus';
import { VitePWA } from 'vite-plugin-pwa';

const isSvelteCheck = process.argv.some((argument) =>
    argument.includes('svelte-check'),
);

if (isSvelteCheck) {
    process.env.LARAVEL_BYPASS_ENV_CHECK ??= '1';
}

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        inertia(),
        tailwindcss(),
        svelte(),
        wayfinder({
            formVariants: true,
        }),
        VitePWA({
            strategies: 'injectManifest',
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            manifest: false,
            srcDir: 'resources/pwa',
            outDir: 'public',
            buildBase: '/',
            filename: 'sw.ts',
            scope: '/',
            devOptions: {
                enabled: false,
            },
            injectManifest: {
                globPatterns: ['**/*.{js,css,html,svg,woff2}'],
                dontCacheBustURLsMatching: /^build\/assets\//,
                maximumFileSizeToCacheInBytes: 4 * 1024 * 1024,
                minify: false,
            },
        }),
    ]),
    server: {
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
            ],
        },
    },
    lint: {
        ignorePatterns: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'resources/pwa/**',
            'tailwind.config.js',
            'resources/js/actions/**',
            'resources/js/components/ui/*',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: 'css',
        ignorePatterns: [
            '.github/**',
            'composer.json',
            'resources/js/components/ui/*',
            'resources/views/mail/*',
        ],
        sortTailwindcss: {
            functions: ['clsx', 'cn', 'cva'],
            entryPoint: 'resources/css/app.css',
        },
    },
});
