import {ref, computed} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {_Deck, _Tag, _BasicCard, _CardGroup} from "../interfaces/inter-sp";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import useDeckGroupLists from "./useDeckGroupLists";

export default function (action = '') {
  const ajax             = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxCreate       = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxUpdate       = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxTrash        = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxDelete       = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  let newItem            = ref<_BasicCard>({
    id        : '',
    deck      : null,
    tags      : [],
    question  : '',
    answer    : '',
    x_position: 0,
    y_position: 0,
    created_at: '',
    updated_at: '',
    deleted_at: '',
  });
  let cardGroup          = ref<_CardGroup>({
    tags          : [],
    id            : 0,
    deck          : null,
    reverse       : false,
    bg_image      : 0,
    name          : '',
    group_type    : '',
    whole_question: '',
    scheduled_at  : '',
    created_at    : '',
    updated_at    : '',
    deleted_at    : '',
  });
  let newCardScheduleNow = ref(false);

  const create = () => {xhrCreateNew();}

  const xhrCreateNew = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          card        : newItem.value,
          cardGroup   : cardGroup.value,
          schedule_now: newCardScheduleNow.value,
        }
      ],
      what: "admin_sp_ajax_admin_create_new_basic_card",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        // handleAjax.success(done);
        // useDeckGroupLists().load();
        // newItem.value.name      = '';
        // newItem.value.tags      = [];
        // newItem.value.deckGroup = null;
        // xhrLoad();
      },
      funcFailue(done) {
        handleAjax.error(done);
      },
    });
  };

  return {
    ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
    create, cardGroup,
    newItem, newCardScheduleNow,
  };
}