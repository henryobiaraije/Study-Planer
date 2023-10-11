import {ref, watch} from "vue";
import type {_Ajax} from "@/classes/HandleAjax";
import type {_Card, _CardGroup} from "@/interfaces/inter-sp";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import RegexHelper from "@/classes/RegexHelper";
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
    let items = ref<Array<_Card>>([]);
    let cardGroup = ref<_CardGroup>({
        tags: [],
        cards: [],
        id: 0,
        deck: null,
        reverse: false,
        bg_image_id: 0,
        name: '',
        group_type: '',
        whole_question: '',
        scheduled_at: '',
    });
    let setBgAsDefault = ref(false);

    const createOrUpdate = () => {
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
                    cards: items.value,
                    cardGroup: cardGroup.value,
                    set_bg_as_default: setBgAsDefault.value,
                }
            ],
            what: "admin_sp_ajax_admin_create_new_gap_card",
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
                    cards: items.value,
                    cardGroup: cardGroup.value,
                    set_bg_as_default: setBgAsDefault.value,
                }
            ],
            what: "admin_sp_ajax_admin_update_gap_card",
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
                what: "admin_sp_ajax_admin_load_basic_card",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess) {
                    handleAjax.stop();
                    const hold: _CardGroup = done.data.card_group;
                    if (hold.cards.length > 0) {
                        // item.value = hold.cards[0];
                    }
                    // cardGroup.value = hold;
                    cardGroup.value.whole_question = hold.whole_question;
                    cardGroup.value.cards = hold.cards;
                    cardGroup.value.name = hold.name;
                    cardGroup.value.created_at = hold.created_at;
                    cardGroup.value.updated_at = hold.updated_at;
                    cardGroup.value.tags = hold.tags;
                    cardGroup.value.cards_count = hold.cards_count;
                    cardGroup.value.deck = hold.deck;
                    cardGroup.value.bg_image_id = hold.bg_image_id;
                    cardGroup.value.group_type = hold.group_type;
                    cardGroup.value.deleted_at = hold.deleted_at;
                    cardGroup.value.id = hold.id;
                    cardGroup.value.card_group_edit_url = hold.card_group_edit_url;
                    cardGroup.value.reverse = hold.reverse;
                    cardGroup.value.scheduled_at = hold.scheduled_at;
                    items.value = hold.cards;
                    // console.log({hold})
                    resolve(done.data);
                },
                funcFailue(done) {
                    // handleAjax.error(done);
                    reject();
                },
            });
        });
    };

    watch(cardGroup.value, (current, old) => {
        const wholeQuestion = cardGroup.value.whole_question;
        const cards = RegexHelper.getItemsFromGapWholeQuestion(wholeQuestion, items.value);
        console.log({items});
        items.value = cards;
    })

    return {
        ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
        createOrUpdate, cardGroup, load,
        items, setBgAsDefault,
    };
}