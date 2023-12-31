import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import {ref} from "@vue/composition-api";
import useDeckGroupLists from "./useDeckGroupLists";
import {_Tag} from "../interfaces/inter-sp";

export default function () {
  let groupName = ref('');
  const ajax    = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const newTags = ref<Array<_Tag>>([]);

  const xhrCreateNewDeckGroup = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajax.value);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          deck_group_name: groupName.value,
          tags           : newTags.value,
        }
      ],
      what: "admin_sp_ajax_admin_create_new_deck_group",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        useDeckGroupLists().load();
        groupName.value = '';
        newTags.value   = [];
        // vdata.vEndpoints.create = {
        //   endpoint   : '',
        //   name       : '',
        //   id         : '',
        //   show       : false,
        //   editCounter: 0,
        // };
        // vdis.xhrLoadEndpoints();
      },
      funcFailue(done) {
        handleAjax.error(done);
        // vdata.tableData.isLoading = false;
      },
    });
  };

  return {
    ajax,
    groupName,
    xhrCreateNewDeckGroup,
    newTags,
  };
}