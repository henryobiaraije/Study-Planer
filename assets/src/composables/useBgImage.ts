import {ref} from "@vue/composition-api";
import {decl} from "postcss";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";

declare var wp;
export default function () {
  const ajaxLoad = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });

  const pickImage      = (button_text: any, header_text: any): Promise<{ id: number, url: number }> => {
    return new Promise((resolve, reject) => {
      let frame = wp.media({
        title   : header_text,
        button  : {
          text: button_text
        },
        multiple: false  // Set to true to allow multiple files to be selected
      });
      frame.open();
      frame.on("select", function () {
        let attachment = frame.state().get("selection").first().toJSON();
        let img_url    = attachment.url;
        let img_att_id = attachment.id;
        let result     = {
          id : img_att_id,
          url: img_url
        };
        resolve(result);
      });
      /**
       * Example
       * this.pick_image_from_media("Pick", "Pick Background Image", function (result) {
                dis.reset();
                if (vdata.html_data.only_as_default) {
                    vdata.html_data_default.image = result.url;

                } else {
                    vdata.html_data.image = result.url;
                }
            });

       */
    });
  }
  const loadedImageUrl = ref('');
  const loadedImageId  = ref(0);

  const xhrLoadImage = (id: number) => {
    loadedImageId.value          = id;
    const handleAjax: HandleAjax = new HandleAjax(ajaxLoad.value);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          id,
        }
      ],
      what: "admin_sp_ajax_admin_load_image_attachment",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.stop();
        loadedImageUrl.value = done.data;
      },
      funcFailue(done) {
        handleAjax.stop();
      },
    });
  };

  return {
    pickImage, loadedImageUrl, loadedImageId, xhrLoadImage,
    ajaxLoad,
  }

}