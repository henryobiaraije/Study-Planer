import {ref, reactive} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {vdata} from "../admin/admin-deck-groups";

export default function () {
  const ajax = reactive<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });

  let timezones    = ref<Array<{ [key: string]: string }>>([]);
  let userTimeZone = ref('');

  const loadTimezones = () => {
    if (timezones.value.length < 1) {
      xhrGetTimezones();
    }
  };

  const updateUserTimezone = () => {
    xhrUpdateUserTimezone();
  }

  const xhrGetTimezones       = () => {

    const handleAjax: HandleAjax = new HandleAjax(ajax);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {}
      ],
      what: "admin_sp_ajax_admin_get_timezones",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.stop();
        timezones.value    = done.data.timezones;
        userTimeZone.value = done.data.user_timezone;
      },
      funcFailue(done) {
        handleAjax.stop();
      },
    });
  };
  const xhrUpdateUserTimezone = () => {

    const handleAjax: HandleAjax = new HandleAjax(ajax);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          timezone: userTimeZone.value,
        }
      ],
      what: "admin_sp_ajax_admin_update_user_timezone",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
      },
      funcFailue(done) {
        handleAjax.stop();
      },
    });
  };

  return {
    ajax, timezones, loadTimezones, userTimeZone,
    updateUserTimezone,
  };
}