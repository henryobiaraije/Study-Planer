import type {_Ajax} from "@/classes/HandleAjax";
import {ref} from "vue";
import type {_Card, _CardGroup} from "@/interfaces/inter-sp";
import Common from "@/classes/Common";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import {Store} from "@/static/store";
import {spClientData} from "@/functions";

export default function (cardGroupId = 0) {
    const ajax = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxCreate = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxUpdate = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxTrash = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxDelete = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    let item = ref<_Card>({
        id: 0,
        question: '',
        answer: '',
        c_number: 'c1',
        hash: Common.getRandomString(),
    });
    let cardGroup = ref<_CardGroup>({
        tags: [],
        cards: [],
        id: 0,
        deck: null,
        topic: null,
        reverse: false,
        bg_image_id: 0,
        name: '',
        group_type: '',
        whole_question: '',
        scheduled_at: '',
        created_at: '',
        updated_at: '',
        deleted_at: '',
        collection: 0,
    });
    let setBgAsDefault = ref(false);

    const createOrUpdate = () => {
        console.log({cardGroupId});
        if (cardGroupId > 0) {
            xhrUpdate();
        } else {
            xhrCreate();
        }
    }
    const load = () => {
        return new Promise((resolve, reject) => {
            if (cardGroupId > 0) {
                xhrLoad().then((res) => {
                    resolve(res);
                }).catch(() => {
                    reject();
                });
            } else {
                resolve(0);
            }
        });
    }

    const xhrCreate = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
        new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    card: item.value,
                    cardGroup: cardGroup.value,
                    set_bg_as_default: setBgAsDefault.value,
                }
            ],
            what: "admin_sp_pro_ajax_admin_create_new_basic_card",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess) {
                handleAjax.success(done);
                window.location = done.data;
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrUpdate = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
        new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    card: item.value,
                    cardGroup: cardGroup.value,
                    set_bg_as_default: setBgAsDefault.value,
                }
            ],
            what: "admin_sp_pro_ajax_admin_update_basic_card",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess) {
                handleAjax.success(done);
                // window.location = done.data;
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrLoad = () => {
        return new Promise((resolve, reject) => {
            const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
            new Server().send_online({
                data: [
                    spClientData().nonce,
                    {
                        card_group_id: cardGroupId,
                    }
                ],
                what: "admin_sp_pro_ajax_admin_load_basic_card",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess) {
                    handleAjax.success(done);
                    const hold: _CardGroup = done.data.card_group;
                    if (hold.cards.length > 0) {
                        item.value = hold.cards[0];
                    }
                    cardGroup.value = hold;
                    console.log({hold})
                    resolve(done.data);
                },
                funcFailue(done) {
                    // handleAjax.error(done);
                    reject();
                },
            });
        });
    };

    return {
        ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
        createOrUpdate, cardGroup, load,
        item, setBgAsDefault,
    };
}