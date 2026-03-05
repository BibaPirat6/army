import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/structure.css',
                'resources/js/structure.js',
            ],
            refresh: true,
            // buildDirectory:"build"
        }),
        tailwindcss(),

    ],
    // для клиента
    // server: {
    //     host: '0.0.0.0',
    //     port: 5173,
    //     hmr:{
    //         host: '192.168.0.132',
    //         port:5173,
    //         protocol: 'ws'
    //     },
    //     cors:true
    // },
    build: {
        manifest: "manifest.json",
        outDir: "public/build",
        // rollupOptions:{
        //     input:{
        //         app:"resources/css/app.css"
        //     }
        // }
    },
    // http://military запуск через apache
    // server: {
    //     host: '127.0.0.1',
    //     port: 5173,
    //     strictPort: true,
    //     hmr: {
    //         host: 'military',
    //     },
    //     cors: {
    //         origin: ['http://military'],
    //     },
    // },
    // http://localhost:8000 запуск php artisan serve - apache не запускать 
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
    }

    // тест на своем пк для клиента
    // server: {
    //     host: '0.0.0.0',
    //     port: 5173,
    //     strictPort: true,
    //     hmr: {
    //         host: '192.168.0.16',
    //     },
    // }


});
