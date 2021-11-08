import {Store} from "./store";


export interface ServerValues {
  data: any,
  action: string
}

export interface InterSendOnlineFileFormat {
  name: string,
  file: File
}

export interface InterSendOnline {
  formId?: string,
  dontShowHtml?: boolean,
  what: string,
  data: any,
  isModal?: boolean
  files?: Array<InterSendOnlineFileFormat>,

  funcBefore?(): void,

  funcOnProgress?(progress: InterFuncProgress): void,

  funcSuccess?(done: InterFuncSuccess): void,

  funcFailue?(done: InterFuncFailure): void,

}

export interface InterServerResponse {
  data: any;
  isSuccess: boolean;
  message: string;
}

export interface InterFuncBeforeSend {
  beforeSending?(): void,
}

export interface InterFuncProgress {
  loaded: number,
  total: number,
  percentage: number,
}

export interface InterFuncSuccess {
  data: any,
  message: any
}

export interface InterFuncFailure {
  data: any,
  message: any
}

export interface InterReportHtml {
  form: any,
  parent: any,
  button: any,
  good: any,
  bad: any,
  spin: any,
  propParent: any,
  prog: any,
  spanStartText: any,
  spanProgressText: any,
}

export class Server {

  private values: InterSendOnline;
  private html: InterReportHtml;
  private errorOccured: boolean = false;
  private xhr: XMLHttpRequest   = null;

  constructor() {

    return this;
  }

  // send_online(what, dataToSend, funcBefore, funcSuccess, funcFailed, funcEnd, funcError, funcProgress, file_array = false) {
  send_online(values: InterSendOnline) {

    this.values  = values;
    let dis      = this;
    let formData = new FormData();

    let prepareToSend = dis.prepareToSend([values.what, ...values.data]);
    // console.log({prepareToSend});
    if (values.files) {
      let file_array = values.files;
      if (file_array.length > 0) {
        for (let a = 0; a < file_array.length; a++) {
          let name = file_array[a].name;
          let file = file_array[a].file;
          // console.log({name,file});
          formData.append(name, file);
        }
      }
    }
    let ac = Store.action;
    // console.log({ac});
    formData.append("form_data", prepareToSend);
    formData.append("action", Store.action);
    Store.setNewAjaxSending();
    if (this.values.isModal) {
      dis.do_xhr_modal(formData);
    } else {
      dis.do_xhr(formData);
    }
    return this;
  }

  public abortRequest() {
    this.xhr.abort();
  }

  private do_xhr_modal(formData: any,) {
    let dis   = this;
    const xhr = new XMLHttpRequest();
    this.xhr  = xhr;

    xhr.open("POST", Store.serverUrl, true);
    xhr.withCredentials   = true;
    // console.dir(["fd", formData]);
    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) {
        let percent = (e.loaded / e.total) * 100;
        //        console.log("total = "+e.total+" loaded = "+e.loaded+" percent = "+percent);
        //        var roundedNumber = Math.round(number * 10) / 10;
        percent = Math.round(percent * 10) / 10;
        if (dis.values.funcOnProgress) {
          dis.values.funcOnProgress({
            loaded    : e.loaded,
            total     : e.total,
            percentage: percent
          });
          dis.showProg(percent);
        }
      }
    };
    xhr.onload            = function () {
      dis.hideSpin();
      dis.hideProg();
      Store.setAjaxEnded();
      if (this.status == 200) {
        let res      = this.response;
        let response = dis.getResponse(res);
        if (response.isSuccess) {
          dis.showSuccess(response.message);
          setTimeout(function () {
            Store.getJquery("#mp-response-modal").hide();
            setTimeout(function () {
              if (dis.values.funcSuccess) {
                dis.values.funcSuccess({
                  data   : response.data,
                  message: response.message
                });
              }
            }, 500);
          }, 2000);
        } else {
          if (response.data !== null) {
            if (dis.values.funcFailue) {
              dis.values.funcFailue({
                data   : response.data,
                message: response.message
              });
            }
            dis.showFailure(response.message);
          } else {
            if (dis.values.funcFailue) {
              dis.values.funcFailue({
                message: "Internal Server Error",
                data   : ""
              });
            }
            dis.showFailure("Internal Server Error");
          }
        }
      } else {
        if (dis.values.funcFailue) {
          dis.values.funcFailue({
            data   : "",
            message: "Error: " + xhr.statusText
          });
        }
        dis.showFailure("Error: " + xhr.statusText);
      }
    };
    xhr.onerror           = function (e) {
      Store.setAjaxEnded();
      dis.hideSpin();
      dis.hideProg();
//      console.log({errortt: e});
      if (dis.values.funcFailue) {
        dis.values.funcFailue({
          data   : "XHR Error : " + e,
          message: "Error: " + xhr.statusText
        });
      }
      dis.showFailure("Error: " + xhr.statusText);
    };

    xhr.onabort = function (e) {

      dis.hideSpin();
      dis.hideProg();
    };
    if (dis.values.funcBefore) {
      this.values.funcBefore();
    }
    Store.getJquery("#mp-response-modal").slideDown("slow");
    this.prepareHtmlReport();
    this.showSpin();
    xhr.send(formData);

    /** close modal */

  }

  private do_xhr(formData: any,) {
    let dis   = this;
    const xhr = new XMLHttpRequest();
    this.xhr  = xhr;
    xhr.open("POST", Store.serverUrl, true);
    xhr.withCredentials   = true;
    // console.dir(["fd", formData]);
    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) {
        let percent = (e.loaded / e.total) * 100;
        //        console.log("total = "+e.total+" loaded = "+e.loaded+" percent = "+percent);
        //        var roundedNumber = Math.round(number * 10) / 10;
        percent = Math.round(percent * 10) / 10;
        if (dis.values.funcOnProgress) {
          dis.values.funcOnProgress({
            loaded    : e.loaded,
            total     : e.total,
            percentage: percent
          });
          dis.showProg(percent);
        }
      }
    };
    xhr.onload            = function () {
      Store.setAjaxEnded();
      dis.hideSpin();
      dis.hideProg();
      if (this.status == 200) {
        let res      = this.response;
        let response = dis.getResponse(res);
        if (response.isSuccess) {

          if (dis.values.funcSuccess) {
            dis.values.funcSuccess({
              data   : response.data,
              message: response.message
            });
          }
          dis.showSuccess(response.message);
        } else {
          if (response.data !== null) {
            if (dis.values.funcFailue) {
              dis.values.funcFailue({
                data   : response.data,
                message: response.message
              });
            }
            dis.showFailure(response.message);
          } else {
            if (dis.values.funcFailue) {
              dis.values.funcFailue({
                message: "Internal Server Error",
                data   : ""
              });
            }
            dis.showFailure("Internal Server Error");
          }
        }

      } else {
        if (dis.values.funcFailue) {
          dis.values.funcFailue({
            data   : "",
            message: "Error: " + xhr.statusText
          });
        }
        dis.showFailure("Error: " + xhr.statusText);

      }
    };
    xhr.onerror           = function (e) {
      dis.hideSpin();
      dis.hideProg();
//      console.log({errortt: e});
      if (dis.values.funcFailue) {
        dis.values.funcFailue({
          data   : "XHR Error : " + e,
          message: "Error: " + xhr.statusText
        });
      }
      dis.showFailure("Error: " + xhr.statusText);
      Store.setAjaxEnded();
    };

    xhr.onabort = function (e) {
      dis.hideSpin();
      dis.hideProg();
    };
    if (dis.values.funcBefore) {
      this.values.funcBefore();
    }

    this.prepareHtmlReport();
    this.showSpin();
    xhr.send(formData);
  }

  private getResponse(response: string): InterServerResponse {
    let isSuccess = false;
    let data      = "";
    let message   = "";

    try {
      let json = JSON.parse(response);

      // console.log({json});
      if (json.status == "0") {
        isSuccess = true;
      }

      data    = json.data;
      message = json.message;
    } catch (exception) {
      let dis = this;
      console.error({exception});
    }
    // console.log({
    //     data: data,
    //     message: message,
    //     isSuccess: isSuccess
    // });
    return {
      data     : data,
      message  : message,
      isSuccess: isSuccess
    };
  }

  private prepareToSend(dataToSend: any,) {
    var data: any = {};
    try {
      data[this.VAR_0]  = dataToSend[0];
      data[this.VAR_1]  = dataToSend[1];
      data[this.VAR_2]  = dataToSend[2];
      data[this.VAR_3]  = dataToSend[3];
      data[this.VAR_4]  = dataToSend[4];
      data[this.VAR_5]  = dataToSend[5];
      data[this.VAR_6]  = dataToSend[6];
      data[this.VAR_7]  = dataToSend[7];
      data[this.VAR_8]  = dataToSend[8];
      data[this.VAR_9]  = dataToSend[9];
      data[this.VAR_10] = dataToSend[10];
      data[this.VAR_11] = dataToSend[11];
      data[this.VAR_12] = dataToSend[12];
      data[this.VAR_13] = dataToSend[13];
      data[this.VAR_14] = dataToSend[14];
      data[this.VAR_15] = dataToSend[15];
      data[this.VAR_16] = dataToSend[16];
      data[this.VAR_17] = dataToSend[17];
      data[this.VAR_18] = dataToSend[18];
      data[this.VAR_19] = dataToSend[19];
      data[this.VAR_20] = dataToSend[20];
    } catch (e) {
      // console.log(["preapare error", e]);
    }

    //    let w = this.S_F_WHAT;
    //    let d = this.S_F_DATA;
    //    let prep = {w : what, d : data};
    let prep: any = {};

//    prep[this.S_F_WHAT] = what;
    prep[this.S_F_DATA] = data;
//    console.log({prep, data, dataToSend});
    let ss              = JSON.stringify(prep);
    return ss;
    //    return prep;
  }

  setVariables() {
    this.VAR_0  = "var_0";
    this.VAR_1  = "var_1";
    this.VAR_2  = "var_2";
    this.VAR_3  = "var_3";
    this.VAR_4  = "var_4";
    this.VAR_5  = "var_5";
    this.VAR_6  = "var_6";
    this.VAR_7  = "var_7";
    this.VAR_8  = "var_8";
    this.VAR_9  = "var_9";
    this.VAR_10 = "var_10";
    this.VAR_11 = "var_11";
    this.VAR_12 = "var_12";
    this.VAR_13 = "var_13";
    this.VAR_14 = "var_14";
    this.VAR_15 = "var_15";
    this.VAR_16 = "var_16";
    this.VAR_17 = "var_17";
    this.VAR_18 = "var_18";
    this.VAR_19 = "var_19";
    this.VAR_20 = "var_20";


    this.S_F_WHAT    = "what";
    this.S_F_DATA    = "data";
    this.S_F_STATUS  = "status";
    this.S_F_SUCCESS = "0";
    this.S_F_FAILURE = "1";
    this.S_F_ERROR   = "2";
    this.W_LOGIN     = "what_login";


  }

  prepareHtmlReport() {
    const formId = this.values.formId;
    const form   = Store.getJquery(formId);
    let parent   = form.find(".resp_one_two");
    if (this.values.isModal) {
      parent = Store.getJquery("#mp-response-pupup-parent");
      // console.log({parent});
    }
    const good         = parent.children(".login-report-success");
    const bad          = parent.children(".login-report-failure");
    const button       = parent.children(".mp_submit");
    const spin         = parent.children(".mp_submit").children(".mp_submit_spinner");
    const propParent   = parent.children(".hose_484_prog");
    const prog         = parent.children(".hose_484_prog").children(".hose_839_main_prog");
    const spanStart    = button.children(".mp-response-show-start-text");
    const spanProgress = button.children(".mp-response-show-progress-text");
    this.html          = {
      good            : good,
      bad             : bad,
      propParent      : propParent,
      form            : form,
      parent          : parent,
      prog            : prog,
      spin            : spin,
      button          : button,
      spanProgressText: spanProgress,
      spanStartText   : spanStart
    };
//     console.log({
//         form,parent, good, bad, spin, propParent, prog
//     });
  }

  private showProg(prog: any) { //console.log("prog = "+prog);
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.prog.width(prog + "%");
    this.html.prog.text(prog + "%");
    //    console.log(this.prog);
    this.html.propParent.slideDown("fast");
  }

  hideProg() {
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.propParent.slideUp("slow");

    Store.getJquery(".mp-response-modal-close").on("click", function () {
      Store.getJquery("#mp-response-modal").slideUp("slow");
    });

  }

  showSuccess(text: any) {
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.good.text(text);
    this.html.bad.hide();
    this.html.good.slideDown("slow");
    this.hideSpin();
    this.hideProg();
    let dis = this;

    setTimeout(function () {
      dis.hideAll();
    }, 6000);
  }

  showFailure(text: any) {
    this.errorOccured = true;
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.bad.text(text);
    this.html.good.hide();
    this.html.bad.slideDown("slow");
    this.hideSpin();
    this.hideProg();
    let dis = this;
    // setTimeout(function () {
    //     vue.hideAll();
    // }, 6000);
  }

  hideAll() {
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.bad.hide("slow");
    this.html.good.hide("slow");
    this.hideSpin();
    this.hideProg();
  }

  showSpin() {
    // console.log("Show spin");
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.spin.slideDown("slow");
    this.html.bad.hide("slow");
    this.html.good.hide("slow");
    // console.log(this.html.button);
    this.html.button.addClass("mp-submit-button-dissabled");
    this.html.spanStartText.hide();
    this.html.spanProgressText.show();
  }

  hideSpin() {
    if (this.values.dontShowHtml) {
      return;
    }
    this.html.spin.slideUp("slow");
    this.html.button.removeClass("mp-submit-button-dissabled");
    this.html.spanProgressText.hide();
    this.html.spanStartText.show();
  }

  private VAR_0       = "var_0";
  private VAR_1       = "var_1";
  private VAR_2       = "var_2";
  private VAR_3       = "var_3";
  private VAR_4       = "var_4";
  private VAR_5       = "var_5";
  private VAR_6       = "var_6";
  private VAR_7       = "var_7";
  private VAR_8       = "var_8";
  private VAR_9       = "var_9";
  private VAR_10      = "var_10";
  private VAR_11      = "var_11";
  private VAR_12      = "var_12";
  private VAR_13      = "var_13";
  private VAR_14      = "var_14";
  private VAR_15      = "var_15";
  private VAR_16      = "var_16";
  private VAR_17      = "var_17";
  private VAR_18      = "var_18";
  private VAR_19      = "var_19";
  private VAR_20      = "var_20";
  private S_F_WHAT    = "what";
  private S_F_DATA    = "data";
  private S_F_STATUS  = "status";
  private S_F_SUCCESS = "0";
  private S_F_FAILURE = "1";
  private S_F_ERROR   = "2";
  private W_LOGIN     = "what_login";
}