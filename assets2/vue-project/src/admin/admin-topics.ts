import {compile, createApp} from 'vue'
// import {createApp} from 'vue/dist/vue.esm-bundler.js'
// import {createApp} from 'vue/dist/vue.runtime.esm-browser.js'

import {initDevTools} from '@/miscelenous/init-dev-tools';

let elem = ".admin-topics";

// @ts-ignore
let exist1 = jQuery(elem).length;
let rootElement = document.querySelector(elem);

if (!rootElement) {
    throw new Error(`Element ${elem} not found`);
}
console.log('login33 ', {exist1}, elem);

const app = createApp({
    // template: compile(rootElement.innerHTML),
    data() {
        return {
            message: "Hello Vue!"
        }
    },
    created() {
        // jQuery(".mpereere-vue-loading").css("display", "none");
        console.log(" created admin-topics");
        initDevTools();
    }
});

app.mount('.admin-topics')
