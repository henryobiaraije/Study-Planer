import {ref, computed, watch} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {_Deck, _Tag, _CardGroup, _Card, _TableItem} from "../interfaces/inter-sp";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import useDeckGroupLists from "./useDeckGroupLists";
import RegexHelper from "../classes/RegexHelper";
import TableHelper from "../classes/TableHelper";
// esm
import {createPopper} from '@popperjs/core';

declare var bootstrap;

export default function (cardGroupId = 0) {
  const ajax         = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxCreate   = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxUpdate   = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxTrash    = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxDelete   = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  let items          = ref<Array<_Card>>([]);
  let cardGroup      = ref<_CardGroup>({
    tags          : [],
    cards         : [],
    id            : 0,
    deck          : null,
    reverse       : false,
    bg_image_id   : 0,
    name          : '',
    group_type    : '',
    whole_question: '',
    scheduled_at  : '',
  });
  let setBgAsDefault = ref(false);


  let tableItem          = ref<_TableItem>([]);
  const currentTableData = ref({
    row: 0,
    col: 0,
  });

  const _tAddColumn           = (index = 0) => {
    tableItem.value = TableHelper.addColumn(tableItem.value, index);
    console.log('column added');
    jQuery('.reset-vue').trigger('click');
  }
  const _tAddRow              = (index = 0) => {
    tableItem.value = TableHelper.addRow(tableItem.value, index);
    console.log('column added ', {index}, currentTableData.value);
  }
  const _insertRowBefore      = () => {_tAddRow(currentTableData.value.row);}
  const _insertRowAfter       = () => {_tAddRow((currentTableData.value.row + 1));}
  const _insertColumnBefore   = () => {_tAddColumn(currentTableData.value.col)}
  const _insertColumnAfter    = () => {}
  const _deleteColumn         = () => {}
  const _deleteRow            = () => {}
  const _openTableActionModal = (col: number, row: number, show = true) => {
    console.log('_openTableActionModal', {col, row, show});
    currentTableData.value.row = row;
    currentTableData.value.col = col;
    jQuery('#table-action').show();
    const targetId = '#table-col-row-' + col + '-' + row;
    const target   = document.querySelector(targetId);
    const tooltip  = document.querySelector('#table-action');
    console.log({target, tooltip, targetId});
    const popperInstance = createPopper(target, tooltip as HTMLElement);
  };

  const createOrUpdate = () => {
    if (cardGroupId > 0) {
      xhrUpdate();
    } else {
      xhrCreate();
    }
  }
  const load           = () => {
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
        Store.nonce,
        {
          cards            : items.value,
          cardGroup        : cardGroup.value,
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
        Store.nonce,
        {
          cards            : items.value,
          cardGroup        : cardGroup.value,
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
  const xhrLoad   = () => {
    return new Promise((resolve, reject) => {
      const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
      new Server().send_online({
        data: [
          Store.nonce,
          {
            card_group_id: cardGroupId,
          }
        ],
        what: "admin_sp_ajax_admin_load_table_card",
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
          cardGroup.value.whole_question      = hold.whole_question;
          cardGroup.value.cards               = hold.cards;
          cardGroup.value.name                = hold.name;
          cardGroup.value.created_at          = hold.created_at;
          cardGroup.value.updated_at          = hold.updated_at;
          cardGroup.value.tags                = hold.tags;
          cardGroup.value.cards_count         = hold.cards_count;
          cardGroup.value.deck                = hold.deck;
          cardGroup.value.bg_image_id         = hold.bg_image_id;
          cardGroup.value.group_type          = hold.group_type;
          cardGroup.value.deleted_at          = hold.deleted_at;
          cardGroup.value.id                  = hold.id;
          cardGroup.value.card_group_edit_url = hold.card_group_edit_url;
          cardGroup.value.reverse             = hold.reverse;
          cardGroup.value.scheduled_at        = hold.scheduled_at;
          items.value                         = hold.cards;
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
    const cards         = RegexHelper.getItemsFromGapWholeQuestion(wholeQuestion, items.value);
    console.log({items});
    items.value = cards;
  })

  return {
    ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
    createOrUpdate, cardGroup, load,
    _tAddColumn, _tAddRow, _openTableActionModal,
    _insertRowBefore, _insertRowAfter, _insertColumnBefore, _insertColumnAfter,
    _deleteColumn, _deleteRow,
    tableItem,
    items, setBgAsDefault,
  };
}