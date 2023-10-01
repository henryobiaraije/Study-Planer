import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import {ref, onMounted} from "@vue/composition-api";
import Cookies from 'js-cookie';
import {vdata} from "../admin/admin-deck-groups";
import {_Tag} from "../interfaces/inter-sp";

export default function (canCreate = true) {
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
  //
  const newName = ref('');
  let results = ref<Array<_Tag>>([]);
  // let results    = ref<Array<any>>([]);
  let sendOnline = null as Server;

  const search = (query: string) => {
    xhrSearchTags(query)
  };
  const addTag = (query: string) => xhrCreate(query);

  const xhrSearchTags = (query: string) => {
    if (null !== sendOnline) {
      sendOnline.abortRequest();
    }
    const handleAjax: HandleAjax = new HandleAjax(ajax.value);
    sendOnline = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          params: {
            per_page: 5,
            page: 1,
            search_keyword: query,
            status: 'publish',
          },
        }
      ],
      what: "admin_sp_ajax_admin_search_tags",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        const items = done.data.details.items;
        results.value = items;
        handleAjax.stop();
      },
      funcFailue(done) {
        results.value = [];
        handleAjax.stop();
      },
    });
  };

  const xhrCreate = (query: string) => {
    if (!canCreate) {
      return;
    }
    // console.log('Crete new tag',{query})
    const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          name: query,
        }
      ],
      what: "admin_sp_ajax_admin_create_tag",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        newName.value = '';
        results.value.push(done.data);
      },
      funcFailue(done) {
        handleAjax.error(done);
      },
    });
  };

  return {
    ajax, newName, search, results, addTag,
  }

}