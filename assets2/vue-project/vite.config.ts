import {fileURLToPath, URL} from 'node:url'

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        vueJsx(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
            // vue: "vue/dist/vue.esm-bundler.js"
            'vue': 'vue/dist/vue.esm-bundler.js',
            // vue: "vue/dist/vue.runtime.esm-browser.js"
        }
    },
    build: {
        sourcemap: true,
        rollupOptions: {
            input: {
                'admin/admin-topics': fileURLToPath(new URL('./src/admin/admin-topics.ts', import.meta.url)),
            },
        },
    },
    // build: {
    //     rollupInputOptions: {
    //         external: ['jquery'],
    //         input: {
    //             'admin-top': fileURLToPath(new URL('./src/admin/admin-topics.ts', import.meta.url)),
    //         }
    //     }
    // }
})
