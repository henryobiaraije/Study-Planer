import Vue, {createApp} from 'vue'
import "@/css/admin/admin-topics.scss";
import {Store} from "@/static/store";
import AdminTopics from "@/admin/AdminTopics.vue";
import {spInitDevTools} from "@/miscelenous/init-dev-tools";
import VueHi from "@/admin/VueHi.vue";

declare var pereere_dot_com_sp_general_localize_4736: any;
const localize = pereere_dot_com_sp_general_localize_4736;
Store.initAdmin({
    serverUrl: localize.ajax_url,
    actionString: localize.ajax_action,
    nonce: localize.nonce,
});
// Vue.config.devtools = true;

function renderVue() {

    let elem = ".admin-topics";

    let rootElement = document.querySelector(elem);

    if (!rootElement) {
        throw new Error(`Element ${elem} not found`);
    }

    // @ts-ignore
    // const app = createApp(AdminTopics);
    const app = createApp(VueHi);
    app.mount('.admin-topics')
    spInitDevTools();
}

renderVue();