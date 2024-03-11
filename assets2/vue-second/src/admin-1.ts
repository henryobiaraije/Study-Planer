import "../src/css/css-test.css";
import "../src/scss/admin-2.scss";
// import "../src/counter.vue";
import Counter from "./counter.vue";
import {createApp} from "vue";
console.log("testing admin 1");



function renderVue() {
    const elemAndComponent = [
        {
            elem: '.admin-1',
            component: Counter
        },
        // {
        //     elem: '.admin-groups',
        //     component: AdminDeckGroups
        // },

    ] as Array<{ elem: string, component: any }>;

    elemAndComponent.forEach((item) => {
        // @ts-ignore
        let elem = jQuery(item.elem);
        if (elem.length) {
            // @ts-ignore
            // const app = createApp(item.component);
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
jQuery(document).ready(function () {
    renderVue();

});
