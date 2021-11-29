import {ref, computed, watch} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {_Deck, _Tag, _CardGroup, _Card, _TableItem} from "../interfaces/inter-sp";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import useDeckGroupLists from "./useDeckGroupLists";
import RegexHelper from "../classes/RegexHelper";
import TableHelper from "../classes/TableHelper";
import Common from "../classes/Common";

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
    whole_question: null,
    scheduled_at  : '',
  });
  let setBgAsDefault = ref(false);

  interface _ImageItem {
    w: number;
    h: number;
    boxes: Array<_ImageBox>;
    hash: string;
  }

  interface _ImageBox {
    x: number;
    y: number;
    h: number;
    w: number;
    text: string;
    hash: string;
  }

  let imageItem = ref<_ImageItem>({
    w    : 300,
    h    : 300,
    hash : Common.getRandomString(),
    boxes: [],
  });

  const currentTableData = ref({
    row: 0,
    col: 0,
  });

  const _AddImage = () => {

  }
  const _AddBox   = () => {
    imageItem.value.boxes.push({
      x   : 0,
      y   : 0,
      w   : 100,
      h   : 30,
      text: '',
      hash: Common.getRandomString(),
    });
    _addBoxEvents();
  }

  const applyCss      = () => {
    applyMainCss();
    applyBoxesCss();
  }
  const applyMainCss  = () => {
    const mainHash = imageItem.value.hash;
    const mainId   = 'main-' + mainHash;
    const styleId  = 'main-style-' + mainHash;
    const css      = `
      <style id="${styleId}">
        #${mainId}{
          height: ${imageItem.value.h}px;
          width: ${imageItem.value.w}px;
        }
      </style>
    `;
    jQuery('head').find('#' + styleId).remove();
    jQuery('head').append(css);
  }
  const applyBoxesCss = () => {
    imageItem.value.boxes.forEach((box: _ImageBox) => {
      const hash    = box.hash;
      const id      = 'sp-box-' + hash;
      const styleId = 'sp-box-style-' + id;
      const css     = `
      <style id="${styleId}">
        #${id}{
          height: ${box.h}px;
          width: ${box.w}px;
          border: 1px solid #fadecc;
          background: #fadecc;
        }
      </style>
    `;
      console.log('box', {hash, css});
      jQuery('head').find('#' + styleId).remove();
      jQuery('head').append(css);
    });
  }

  const _addBoxEvents = () => {
    setTimeout(() => {
      imageItem.value.boxes.forEach((box: _ImageBox, i) => {
        const hash = box.hash;
        const id   = 'sp-box-' + hash;
        // @ts-ignore
        jQuery('#' + id).resizable().draggable().rotatable();
      });
      applyCss();
    }, 1000);
  }
  const _addEvents    = () => {
    // @ts-ignore
    jQuery('.image-area-inner').resizable({
      autoHide: true,
      stop    : function (event, ui) {
        console.log({event, ui});
        const width  = ui.size.width;
        const height = ui.size.height;
        _mainDropped(width, height);
      },
    });
    applyCss();
  }

  const _createOrUpdate = () => {
    if (cardGroupId > 0) {
      xhrUpdate();
    } else {
      xhrCreate();
    }
  }
  const _load           = () => {
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

  const _mainDropped    = (w: number, h: number) => {
    imageItem.value.w = w;
    imageItem.value.h = h;
    applyCss();
  }
  const _refreshPreview = () => {
    // items.value = TableHelper.getItemsFromTable(tableItem.value);
  }

  const xhrCreate = () => {
    cardGroup.value.whole_question = imageItem.value;
    const handleAjax: HandleAjax   = new HandleAjax(ajaxCreate.value);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          cards            : items.value,
          cardGroup        : cardGroup.value,
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
    const handleAjax: HandleAjax   = new HandleAjax(ajaxCreate.value);
    cardGroup.value.whole_question = imageItem.value;
    new Server().send_online({
      data: [
        Store.nonce,
        {
          cards            : items.value,
          cardGroup        : cardGroup.value,
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
          cardGroup.value.whole_question      = hold.whole_question;
          imageItem.value                     = hold.whole_question;
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

  const _cssMain = computed(() => {
    return {
      width : imageItem.value.w,
      height: imageItem.value.h,
    };
  })

  const _cssBox = computed((box: _ImageBox) => {
    return {
      width : box.x,
      height: box.y,
    };
  })

  return {
    ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
    _createOrUpdate, cardGroup, _load, _refreshPreview,
    imageItem, _AddBox,
    items, setBgAsDefault, _addEvents,
  };
}