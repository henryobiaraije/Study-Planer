import {createApp} from 'vue'
// import "@/css/admin/admin-deck-groups.css";
// import {Store} from "@/static/store";
// import {spInitDevTools} from "@/miscelenous/init-dev-tools";
// import AdminDeckGroups from "@/admin/AdminDeckGroups.vue";

declare var pereere_dot_com_sp_pro_general_localize_4736: any;
const localize = pereere_dot_com_sp_pro_general_localize_4736;
// Store.initAdmin({
//     serverUrl: localize.ajax_url,
//     actionString: localize.ajax_action,
//     nonce: localize.nonce,
// });

function renderVue() {

    let elem = ".admin-groups";

    let rootElement = document.querySelector(elem);

    if (!rootElement) {
        throw new Error(`Element ${elem} not found`);
    }

    // @ts-ignore
    // const app = createApp(AdminTopics);
    // const app = createApp(AdminDeckGroups);
    // app.mount(elem)
    // spInitDevTools();

    console.log('renderVue()');
}

jQuery(document).ready(function () {
    renderVue();
});