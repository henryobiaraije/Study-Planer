import {createApp} from 'vue'
import "@/css/admin/admin-topics.scss";
import {Store} from "@/static/store";
import AdminTopics from "@/admin/AdminTopics.vue";

declare var pereere_dot_com_sp_general_localize_4736: any;
const localize = pereere_dot_com_sp_general_localize_4736;
Store.initAdmin({
    serverUrl: localize.ajax_url,
    actionString: localize.ajax_action,
    nonce: localize.nonce,
});

function renderVue() {

    let elem = ".admin-topics";

    let rootElement = document.querySelector(elem);

    if (!rootElement) {
        throw new Error(`Element ${elem} not found`);
    }

    // @ts-ignore
    const app = createApp(AdminTopics);
    app.mount('.admin-topics')

}

renderVue();