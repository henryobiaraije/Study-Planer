import {compile, createApp} from 'vue'
import {initDevTools} from '@/miscelenous/init-dev-tools';
import "@/css/admin/admin-topics.scss";

let elem = ".admin-topics";

let rootElement = document.querySelector(elem);

if (!rootElement) {
    throw new Error(`Element ${elem} not found`);
}

const app = createApp({
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
