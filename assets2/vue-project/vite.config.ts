import {fileURLToPath, URL} from 'node:url'

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
// import postcssNesting from 'postcss-nesting';

// https://vitejs.dev/config/
export default defineConfig({
    mode: 'development',
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
        cssCodeSplit: true,
        rollupOptions: {
            input: {
                'main-admin': fileURLToPath(new URL('./src/admin/main-admin.ts', import.meta.url)),
                // 'admin/admin-topics': fileURLToPath(new URL('./src/admin/admin-topics.ts', import.meta.url)),
                // 'admin/admin-deck-groups': fileURLToPath(new URL('./src/admin/admin-deck-groups', import.meta.url)),
            },
        },
    },
    // css: { postcss: {
    //         plugins: [
    //             postcssNesting
    //         ],
    //     },
    // },
    // build: {
    //     rollupInputOptions: {
    //         external: ['jquery'],
    //         input: {
    //             'admin-top': fileURLToPath(new URL('./src/admin/admin-topics.ts', import.meta.url)),
    //         }
    //     }
    // }
})
