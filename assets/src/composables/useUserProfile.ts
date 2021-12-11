import {ref, reactive} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {vdata} from "../admin/admin-deck-groups";

interface _UserProfile {
  user_email: string,
  user_name: string,
}

export default function () {
  const ajax = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxUpdate = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });

  let profile = ref<_UserProfile>(null);


  const _loadProfile = () => {
    xhrLoadProfile();
  };
  const _updateProfile = () => {
    xhrUpdateProfile();
  }

  const xhrLoadProfile = () => {

    const handleAjax: HandleAjax = new HandleAjax(ajax);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {}
      ],
      what: "front_sp_ajax_admin_load_user_profile",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.stop();
        profile.value = done.data;
      },
      funcFailue(done) {
        handleAjax.stop();
      },
    });
  };
  const xhrUpdateProfile = () => {

    const handleAjax: HandleAjax = new HandleAjax(ajax);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          // timezone: userTimeZone.value,
        }
      ],
      what: "front_sp_ajax_admin_update_user_timezone",
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
    ajax,
    profile,
    _loadProfile, _updateProfile,
  };
}