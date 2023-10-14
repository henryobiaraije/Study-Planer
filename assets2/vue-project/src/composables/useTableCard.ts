// esm
import {createPopper} from '@popperjs/core';
import {ref} from "vue";
import type {_Ajax} from "@/classes/HandleAjax";
import type {_Card, _CardGroup, _TableItem} from "@/interfaces/inter-sp";
import TableHelper from "@/classes/TableHelper";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import {spClientData} from "@/functions";

declare var bootstrap;

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
        topic: null,
        collection: null,
        reverse: false,
        bg_image_id: 0,
        name: '',
        group_type: '',
        whole_question: null,
        scheduled_at: '',
    });
    let setBgAsDefault = ref(false);

    // let tableItem          = ref<_TableItem>([
    //   ["<p>Words</p>", "<p>Comparative</p>", "<p>Superlative</p>"], ["<p>A {{c1:: big }} orange is {{c2:: here}} and this {{c1::right here}}</p>", "<p>A {{c2:: bigger }} orange</p>", "<p><span style=\"color: #3366ff;\">The {{c3:: biggest}}orange</span></p>"],
    //   ["<p>The  {{c4:: tall }} <strong><span style=\"color: #ff9900;\">building</span></strong></p>", "<p>The {{c5:: taller }} building</p>", "<p>The {{c6:: tallest }} building</p>"],
    //   ["<p>The {{c1:: fast }} computer</p>", "<p>The {{c2:: faster }}  <strong><span style=\"color: #99cc00;\">computer</span></strong></p>", "<p>The {{c3:: fastest }} computer</p>"]
    // ]);
    let tableItem = ref<_TableItem>([]);
    const currentTableData = ref({
        row: 0,
        col: 0,
    });

    const _hideActionMenu = () => {
        jQuery('.table-action').hide();
        console.log('hide menu'); //
    }
    const _tAddColumn = (index: number | null = 0) => {
        tableItem.value = TableHelper.addColumn(tableItem.value, index);
        console.log('column added');
        jQuery('.reset-vue').trigger('click');
        _hideActionMenu();
    }
    const _tAddRow = (index: number | null = 0) => {
        tableItem.value = TableHelper.addRow(tableItem.value, index);
        console.log('column added ', {index}, currentTableData.value);
        _hideActionMenu();
    }
    const _insertRowBefore = () => {
        _tAddRow(currentTableData.value.row);
        _hideActionMenu();
    }
    const _insertRowAfter = () => {
        _tAddRow((currentTableData.value.row + 1));
        _hideActionMenu();
    }
    const _insertColumnBefore = () => {
        _tAddColumn(currentTableData.value.col);
        _hideActionMenu();
    }
    const _insertColumnAfter = () => {
        _tAddColumn((currentTableData.value.col + 1));
        _hideActionMenu();
    }
    const _deleteColumn = () => {
        tableItem.value = TableHelper.deleteColumns(tableItem.value, currentTableData.value.col);
        jQuery('.reset-vue').trigger('click');
        _hideActionMenu();
    }
    const _deleteRow = () => {
        tableItem.value = TableHelper.deleteRow(tableItem.value, currentTableData.value.row);
        jQuery('.reset-vue').trigger('click');
        _hideActionMenu();
    }
    const _openTableActionModal = (col: number, row: number, show = true) => {
        console.log('_openTableActionModal', {col, row, show});
        currentTableData.value.row = row;
        currentTableData.value.col = col;
        jQuery('#table-action').show();
        const targetId = '#table-col-row-' + col + '-' + row;
        const target = document.querySelector(targetId);
        const tooltip = document.querySelector('#table-action');
        console.log({target, tooltip, targetId});
        const popperInstance = createPopper(target, tooltip as HTMLElement);
    };
    const _createOrUpdate = () => {
        if (cardGroupId > 0) {
            xhrUpdate();
        } else {
            xhrCreate();
        }
    }
    const _load = () => {

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
    const _refreshPreview = () => {
        items.value = TableHelper.getItemsFromTable(tableItem.value, items.value);
    }
    const xhrCreate = () => {
        cardGroup.value.whole_question = tableItem.value;
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
            what: "admin_sp_ajax_admin_create_new_table_card",
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
        cardGroup.value.whole_question = tableItem.value;
        new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    cards: items.value,
                    cardGroup: cardGroup.value,
                    set_bg_as_default: setBgAsDefault.value,
                }
            ],
            what: "admin_sp_ajax_admin_update_table_card",
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
                    tableItem.value = hold.whole_question;
                    cardGroup.value.cards = hold.cards;
                    cardGroup.value.name = hold.name;
                    cardGroup.value.created_at = hold.created_at;
                    cardGroup.value.updated_at = hold.updated_at;
                    cardGroup.value.tags = hold.tags;
                    cardGroup.value.cards_count = hold.cards_count;
                    cardGroup.value.deck = hold.deck;
                    cardGroup.value.topic = hold.topic;
                    cardGroup.value.collection = hold.collection;
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

    return {
        ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
        _createOrUpdate, cardGroup, _load,
        _tAddColumn, _tAddRow, _openTableActionModal,
        _insertRowBefore, _insertRowAfter, _insertColumnBefore, _insertColumnAfter,
        _deleteColumn, _deleteRow, _refreshPreview,
        tableItem,
        items, setBgAsDefault,
        test: 'one',
    };
}