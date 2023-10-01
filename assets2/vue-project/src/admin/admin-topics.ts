import {createApp} from 'vue'
import {initDevTools} from '@/miscelenous/init-dev-tools';

let elem = ".admin-topics";

// @ts-ignore
let exist1 = jQuery(elem).length;
console.log('login33 ', {exist1}, elem);

createApp({
    created() {
        // jQuery(".mpereere-vue-loading").css("display", "none");
        console.log(" created admin-topics");
        initDevTools();
    }
}).mount('.admin-topics')
