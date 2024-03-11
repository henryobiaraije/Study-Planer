import {reactive, ref} from "vue";
import type {_Ajax} from "@/classes/HandleAjax";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import {spClientData} from "@/functions";
import {toast} from "vue3-toastify";


export default function () {
    const ajax = reactive<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });

    let timezones = ref<Array<{ [key: string]: string }>>([]);
    let userTimeZone = ref('');

    const loadTimezones = () => {
        if (timezones.value.length < 1) {
            xhrGetTimezones();
        }
    };

    const updateUserTimezone = () => {
        xhrUpdateUserTimezone();
    }

    const xhrGetTimezones = () => {

        const handleAjax: HandleAjax = new HandleAjax(ajax);
        new Server().send_online({
            data: [
                spClientData().nonce,
                {}
            ],
            what: "front_sp_pro_ajax_admin_get_timezones",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                timezones.value = done.data.timezones;
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
                spClientData().nonce,
                {
                    timezone: userTimeZone.value,
                }
            ],
            what: "front_sp_pro_ajax_admin_update_user_timezone",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
            },
            funcFailue(done) {
                handleAjax.stop();
                toast.error(done.data.message);
            },
        });
    };

    return {
        ajax, timezones, loadTimezones, userTimeZone,
        updateUserTimezone,
    };
}