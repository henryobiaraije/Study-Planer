import {ref, computed, watch} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {
  _Deck,
  _Tag,
  _CardGroup,
  _Card,
  _TableItem,
  _ImageItem,
  _ImageBox,
  IMAGE_DISPLAY_TYPE
} from "../interfaces/inter-sp";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import useDeckGroupLists from "./useDeckGroupLists";
import RegexHelper from "../classes/RegexHelper";
import TableHelper from "../classes/TableHelper";
import Common from "../classes/Common";
import useBgImage from "./useBgImage";
import {createPopper} from "@popperjs/core";
import ImageHelper from "../classes/ImageHelper";

declare var bootstrap;

interface _AdminSettings {
  mature_card_days: number;
}

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
  const settings = ref<_AdminSettings>(null);

  const _loadSettings = () => {
    return xhrLoad().then(() => {

    });
  }

  const _updateSettings = () => {
    xhrUpdate();
  }

  const xhrUpdate = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxUpdate.value);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          settings: settings.value,
        }
      ],
      what: "admin_sp_pro_ajax_admin_update_settings",
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
      const handleAjax: HandleAjax = new HandleAjax(ajax.value);
      new Server().send_online({
        data: [
          Store.nonce,
          {
            // card_group_id: cardGroupId,
          }
        ],
        what: "admin_sp_pro_ajax_admin_load_settings",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          settings.value = done.data;
          resolve(done.data);
        },
        funcFailue(done) {
          handleAjax.error(done);
          reject();
        },
      });
    });
  };

  return {
    ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
    _loadSettings, _updateSettings,
    settings,
  };
}