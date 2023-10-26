import type {_HoverNotification} from "@/vue-component/enums";
import {ENUM_NOTIFICATION_TYPE} from "@/vue-component/enums";
import type {InterFuncFailure, InterFuncSuccess} from "@/static/server";

declare var bootstrap;
declare var jQuery;

export interface _Ajax {
    sending: boolean;
    error: boolean;
    errorMessage: string;
    success: boolean;
    successMessage: string;
}

export class HandleAjax {
    private ajax: _Ajax = null;
    public showModal: boolean = false;
    public forModal: boolean = false;
    public static hoverNotifications = [] as Array<_HoverNotification>;
    public notify: _HoverNotification = {
        text: '',
        additionalMessage: '',
        show: false,
        key: Math.random().toString(36).substring(2, 7),
        type: ENUM_NOTIFICATION_TYPE.ERROR,
    };

    constructor(ajax?: _Ajax, forModal: boolean = false) {
        this.ajax = ajax;
        if (!ajax) {
            this.ajax = {
                sending: false,
                error: false,
                errorMessage: '',
                success: false,
                successMessage: '',
            };
        }
        this.forModal = forModal;
    }

    public start(): void {
        console.log('start...');
        //ajax-modal
        if (this.forModal) {
            setTimeout(() => {
                const elem = jQuery('#ajax-modal')[0];
                let myModal = new bootstrap.Modal(elem);
                myModal.show();
                elem.addEventListener('hidden.bs.modal', function (event) {

                });
                jQuery('body').append(elem);
                console.log('start');
            }, 100);
        }
        this.ajax.sending = true;
        this.ajax.errorMessage = '';
        this.ajax.error = false;
        this.ajax.successMessage = '';
        this.ajax.success = false;
        this.showModal = this.forModal;
    }

    public error(data: InterFuncFailure): void {
        this.ajax.errorMessage = data.message;
        this.ajax.error = true;
        this.ajax.sending = false;
        this.notify.type = ENUM_NOTIFICATION_TYPE.ERROR;
        this.notify.text = data.message;
        // HandleAjax.hoverNotifications.push(this.notify);
        jQuery('body').trigger('addNotification', this.notify);
    }

    public success(data: InterFuncSuccess<any>): void {
        this.ajax.successMessage = data.message;
        this.ajax.success = true;
        this.ajax.sending = false;
        const dis = this;
        this.notify.type = ENUM_NOTIFICATION_TYPE.SUCCESS;
        this.notify.text = data.message;
        // HandleAjax.hoverNotifications.push(this.notify);
        // console.log('adding trigger', this.notify);
        jQuery('body').trigger('addNotification', this.notify);
        console.log('success', data.message);

        setTimeout(() => {
            dis.showModal = false;
            this.ajax.success = false;
        }, 6000);
    }

    public successWithoutNotification(data: InterFuncSuccess<any>): void {
        this.ajax.successMessage = data.message;
        this.ajax.success = true;
        this.ajax.sending = false;
        const dis = this;
        this.notify.type = ENUM_NOTIFICATION_TYPE.SUCCESS;
        this.notify.text = data.message;
        // HandleAjax.hoverNotifications.push(this.notify);
        // console.log('adding trigger', this.notify);
        jQuery('body').trigger('addNotification', this.notify);

        setTimeout(() => {
            dis.showModal = false;
            this.ajax.success = false;
        }, 6000);
    }

    public stop(): void {
        this.ajax.sending = false;
    }
}
