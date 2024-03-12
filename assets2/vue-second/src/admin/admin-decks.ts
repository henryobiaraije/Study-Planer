// import "../src/css/css-test.css";
// import "../src/scss/admin-2.scss";
// import "../src/counter.vue";
// import Counter from "./counter.vue";
import {createApp} from "vue";
import AdminTopics from "@/admin/AdminTopics.vue";

import {Store} from "@/static/store";
import "../scss/default.scss";
import AdminDecks from "@/admin/AdminDecks.vue";

console.log("testing admin 1");

declare var pereere_dot_com_sp_pro_general_localize_4736: any;
const localize = pereere_dot_com_sp_pro_general_localize_4736;
Store.initAdmin({
    serverUrl: localize.ajax_url,
    actionString: localize.ajax_action,
    nonce: localize.nonce,
});


function renderVue() {
    const elemAndComponent = [
        {
            elem: '.admin-decks',
            component: AdminDecks
        },
    ] as Array<{ elem: string, component: any }>;

    elemAndComponent.forEach((item) => {
        // @ts-ignore
        let elem = //@ts-ignore//@ts-ignore
            jQuery(item.elem);
        if (elem.length) {
            // @ts-ignore
            const app = createApp(item.component);
            // app.use(Vue3Toastify, {
            //     autoClose: 4000,
            //     position: 'bottom-right'
            // } as ToastContainerOptions);
            // app.use(vuetify);
            // app.use(CKEditor);
            // app.use(QuillEditor);
            app.mount(item.elem);
        }
    });

}

// @ts-ignore
//@ts-ignore
jQuery(document).ready(function () {
    renderVue();
});
