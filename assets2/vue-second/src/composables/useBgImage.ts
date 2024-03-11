import type {_Ajax} from "@/classes/HandleAjax";
import {ref} from "vue";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import {Store} from "@/static/store";
import {spClientData} from "@/functions";

// @ts-ignore
declare var wp;
export default function () {
    const ajaxLoad = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });

    const pickImage = (button_text: any, header_text: any): Promise<{ id: number, url: string }> => {
        if (undefined === button_text) {
            button_text = "Pick";
        }
        if (undefined === header_text) {
            header_text = "Pick an image";
        }
        return new Promise((resolve, reject) => {
            console.dir(wp.media)
            // debugger;
            let frame = wp.media({
                title: header_text,
                button: {
                    text: button_text
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });
            frame.open();
            frame.on("select", function () {
                let attachment = frame.state().get("selection").first().toJSON();
                let img_url = attachment.url;
                let img_att_id = attachment.id;
                let result = {
                    id: img_att_id,
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
    const loadedImageId = ref(0);

    const xhrLoadImage = (id: number) => {
        loadedImageId.value = id;
        const handleAjax: HandleAjax = new HandleAjax(ajaxLoad.value);
        new Server().send_online({
            data: [
                Store.nonce,
                {
                    id,
                }
            ],
            what: "admin_sp_pro_ajax_admin_load_image_attachment",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                loadedImageUrl.value = done.data;
            },
            funcFailue(done) {
                handleAjax.stop();
            },
        });
    };

    const xhrUploadImage = (file: File): Promise<{
        id: number;
        url: string;
    }> => {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('image', file);
            //@ts-ignore
jQuery
                .ajax({
                    headers: {
                        'X-WP-Nonce': spClientData().localize.rest_nonce,
                    },
                    url: "/wp-json/study-planner-pro/v1" + "/file-upload/image",
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    cache: false,
                    data: formData,
                    beforeSend: function (xhr) {
                        ajaxLoad.value.sending = true;
                    },
                    success: function (data: { attachment_id: number, url: string }) {
                        // console.log("success", {data})
                        resolve({
                            id: data.attachment_id,
                            url: data.url,
                        });
                        ajaxLoad.value.sending = false;
                    },
                    error: function (request, status, error) {
                        // console.log("error", {request, status, error})
                        reject(error);
                        ajaxLoad.value.sending = false;
                    }
                })
        });
    }

    return {
        pickImage, loadedImageUrl, loadedImageId, xhrLoadImage,
        ajaxLoad,
        xhrUploadImage
    }

}