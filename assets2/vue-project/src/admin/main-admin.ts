import {createApp} from 'vue'
import "@/css/admin/admin-topics.scss";
import {Store} from "@/static/store";
import AdminTopics from "@/admin/AdminTopics.vue";
import {spInitDevTools} from "@/miscelenous/init-dev-tools";
import AdminDeckGroups from "@/admin/AdminDeckGroups.vue";
import AdminDecks from "@/admin/AdminDecks.vue";

declare var pereere_dot_com_sp_general_localize_4736: any;
const localize = pereere_dot_com_sp_general_localize_4736;
Store.initAdmin({
    serverUrl: localize.ajax_url,
    actionString: localize.ajax_action,
    nonce: localize.nonce,
});

// Vue.config.devtools = true;

function renderVue() {
    const elemAndComponent = [
        {
            elem: '.admin-topics',
            component: AdminTopics,
        },
        {
            elem: '.admin-groups',
            component: AdminDeckGroups
        },
        {
            elem: '.admin-decks',
            component: AdminDecks
        },
    ] as Array<{ elem: string, component: any }>;

    elemAndComponent.forEach((item) => {
        let elem = jQuery(item.elem);
        if (elem.length) {
            // @ts-ignore
            const app = createApp(item.component);
            app.mount(item.elem);
        }
    });

    // let rootElement = document.querySelector(elem);
    //
    // if (!rootElement) {
    //     throw new Error(`Element ${elem} not found`);
    // }
    //
    // // @ts-ignore
    // // const app = createApp(AdminTopics);
    // const app = createApp(AdminTopics);
    // app.mount('.admin-topics')
    spInitDevTools();
}

jQuery(document).ready(function () {
    renderVue();
});
