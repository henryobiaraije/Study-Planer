import {ref, computed} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {_Deck, _Tag, _BasicCard, _CardGroup} from "../interfaces/inter-sp";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import useDeckGroupLists from "./useDeckGroupLists";

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
  let item           = ref<_BasicCard>({
    id        : 0,
    question  : '',
    answer    : '',
    x_position: 0,
    y_position: 0,
    created_at: '',
    updated_at: '',
    deleted_at: '',
  });
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
    created_at    : '',
    updated_at    : '',
    deleted_at    : '',
  });
  let setBgAsDefault = ref(false);

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
          card             : item.value,
          cardGroup        : cardGroup.value,
          set_bg_as_default: setBgAsDefault.value,
        }
      ],
      what: "admin_sp_ajax_admin_create_new_basic_card",
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
          card             : item.value,
          cardGroup        : cardGroup.value,
          set_bg_as_default: setBgAsDefault.value,
        }
      ],
      what: "admin_sp_ajax_admin_update_basic_card",
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
    item,  setBgAsDefault,
  };
}