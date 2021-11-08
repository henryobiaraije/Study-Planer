import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import { Store } from "../static/store";

export default function () {
  const groupName = '';
  const ajax      = {
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',

  } as _Ajax;

  const xhrCreateEndpoint = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajax);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          deck_group_name: groupName,
          // name    : vdata.vEndpoints.create.name,
          // endpoint: vdata.vEndpoints.create.endpoint,
        }
      ],
      what: "admin_sp_ajax_admin_create_new_deck_group",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
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
    xhrCreateEndpoint,
  };
}