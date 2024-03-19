// import "../src/css/css-test.css";
// import "../src/scss/admin-2.scss";
// import "../src/counter.vue";
// import Counter from "./counter.vue";
import {createApp} from "vue";

import {Store} from "@/static/store";
import "../scss/default.scss";
import "../admin/admin-table-card.css";
import 'vue3-toastify/dist/index.css';
// import Vue3Toastify, {toast, type ToastContainerOptions} from "vue3-toastify";
import 'vuetify/styles'
import {createVuetify} from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives';
import ShortcodeUserDashboard from "@/admin/ShortcodeUserDashboard.vue";

console.log("testing admin 1");

declare var pereere_dot_com_sp_pro_general_localize_4736: any;
const localize = pereere_dot_com_sp_pro_general_localize_4736;
Store.initAdmin({
    serverUrl: localize.ajax_url,
    actionString: localize.ajax_action,
    nonce: localize.nonce,
});

const myCustomLightTheme = {
    dark: false,
    colors: {
        background: '#FFFFFF',
        surface: '#FFFFFF',
        primary: 'rgb(233, 114, 37)',
        'primary-darken-1': 'rgb(190, 86, 19)',
        secondary: '#03DAC6',
        'secondary-darken-1': '#018786',
        error: '#B00020',
        info: '#2196F3',
        success: '#4CAF50',
        warning: '#FB8C00',
    },
};

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'myCustomLightTheme',
        themes: {
            myCustomLightTheme,
        }
    },
});


function renderVue() {
    const elemAndComponent = [
        {
            elem: '.sp-user-dashboard',
            component: ShortcodeUserDashboard
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
            app.use(vuetify);
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
