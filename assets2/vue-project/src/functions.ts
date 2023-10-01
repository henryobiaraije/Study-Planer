import type {_Localize} from "@/interfaces/inter-sp";
import {Store} from "@/static/store";

export function spGetNewAjax() {
    return {
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    };
}

declare var pereere_dot_com_sp_general_localize_4736: any;

export function spClientData(): _Localize {
    const localize = pereere_dot_com_sp_general_localize_4736;
    Store.initAdmin({
        serverUrl: localize.ajax_url,
        actionString: localize.ajax_action,
        nonce: localize.nonce,
    });

    return {
        serverUrl: localize.ajax_url,
        actionString: localize.ajax_action,
        nonce: localize.nonce
    };
}