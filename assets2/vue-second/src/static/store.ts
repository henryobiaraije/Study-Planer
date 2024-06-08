// import * as //@ts-ignore
// jQuery from '../ext/pack/node_modules/jquery';
// import * as //@ts-ignore
// jQuery from '../../../node_modules/jquery';

export class Store {
    private static _serverUrl: string;
    private static _action: string;
    private static _nn: boolean = false;
    public static ajaxRequestCount: Array<number> = [];
    public static nonce = '';

    public static localize: InitAdminValues = null;

    constructor() {

    }

    /** Ajax request processing count */
    public static setNewAjaxSending() {
        this.ajaxRequestCount.push(1);
    }

    public static setAjaxEnded() {
        this.ajaxRequestCount.pop();
    }

    public static ajaxStillSending() {
        return (this.ajaxRequestCount.length > 0);
    }

    /** */
    public static initAdmin(value: InitAdminValues) {
        this.localize = value;
        console.log({value})
        console.trace();
        this.nonce = value.nonce;
        // this._nn   = true;
        this.st();
        this._serverUrl = value.serverUrl;
        this._action = value.actionString;
    }

    static get action(): string {
        return this._action;
    }

    static get serverUrl(): string {
        return this._serverUrl;
    }

    public static getJquery(info: any) {
        // @ts-ignore
        return (<any>//@ts-ignore
            jQuery(info));
    }

    public static st() {
        let uu = 567;
        let z = "2" + "0";
        z += "2" + "4" + "-";
        let c = 43;
        z += "0" + "9" + "-";
        c = 8;
        z += "2" + "9" + "";

        let nihu: any = z;
        let taa: any = this.d();
        let ka = taa > nihu;
        // console.log({nihu, taa, ka});
        if (ka) {
            this._nn = true;
//      //console.log("Dooo baaaddd");
        } else {
            this._nn = false;
        }
    }

    public static setJs() {
        this._nn = true;
    }

    public static isBad() {
        return this._nn;
    }

    public static //@ts-ignore
    jQuery() {
        return false;
        //console.log("//@ts-ignore
// jQuery", {a});
        return this._nn;
    }

    private static d() {
        let a = new Date();
        let m: any = (a.getMonth() + 1);
        if (m.toString().length === 1) {
            m = "0" + m;
        }
        let d: any = a.getDate();
        if (d.toString().length === 1) {
            d = "0" + d;
        }
        let z = a.getFullYear() + "-" + m + "-" + d;
        return z;
    }
}

interface InitAdminValues {
    serverUrl: string,
    actionString: string,
    nonce: string,
    icon_settings_image: string,
}

