import {createApp} from 'vue'
import "@/css/admin/admin-topics.scss";
import {Store} from "@/static/store";
import AdminTopics from "@/admin/AdminTopics.vue";
import {spInitDevTools} from "@/miscelenous/init-dev-tools";
import AdminDeckGroups from "@/admin/AdminDeckGroups.vue";
import AdminDecks from "@/admin/AdminDecks.vue";
import AdminTags from "@/admin/AdminTags.vue";
import AdminAllCards from "@/admin/AdminAllCards.vue";
import AdminBasicCard from "@/admin/AdminBasicCard.vue";
import AdminGapCard from "@/admin/AdminGapCard.vue";
import AdminTableCard from "@/admin/AdminTableCard.vue";
import AdminImageCard from "@/admin/AdminImageCard.vue";
import AdminCollections from "@/admin/AdminCollections.vue";


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
        // {
        //     elem: '.admin-groups',
        //     component: AdminDeckGroups
        // },
        // {
        //     elem: '.admin-decks',
        //     component: AdminDecks
        // },
        // {
        //     elem: '.admin-tags',
        //     component: AdminTags
        // },
        // {
        //     elem: '.admin-all-cards',
        //     component: AdminAllCards
        // },
        // {
        //     elem: '.admin-basic-card',
        //     component: AdminBasicCard
        // },
        // {
        //     elem: '.admin-gap-card',
        //     component: AdminGapCard
        // },
        {
            elem: '.admin-table-card',
            component: AdminTableCard
        },
        // {
        //     elem: '.admin-image-card',
        //     component: AdminImageCard
        // },
        // {
        //     elem: '.admin-collections',
        //     component: AdminCollections
        // },
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
