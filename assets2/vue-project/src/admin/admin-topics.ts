import {createApp} from 'vue'
import {initDevTools} from '@/miscelenous/init-dev-tools';
import "@/css/admin/admin-topics.scss";
import PickImage from "@/vue-component/PickImage.vue";
import {Store} from "@/static/store";

declare var pereere_dot_com_sp_general_localize_4736: any;
const localize = pereere_dot_com_sp_general_localize_4736;
Store.initAdmin({
    serverUrl: localize.ajax_url,
    actionString: localize.ajax_action,
    nonce: localize.nonce,
});

(function () {

    let elem = ".admin-topics";

    let rootElement = document.querySelector(elem);

    if (!rootElement) {
        throw new Error(`Element ${elem} not found`);
    }

    const app = createApp({
        data() {
            return {
                message: "Hello Vue!",
                imageId: 1651,
            }
        },
        created() {
            // jQuery(".mpereere-vue-loading").css("display", "none");
            console.log(" created admin-topics");
            initDevTools();
        },
    });

    // @ts-ignore
    app.component('pick-image', PickImage);

    app.mount('.admin-topics')

})();
