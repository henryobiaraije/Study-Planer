"use strict";
import {Store} from "../static/store";
import {InterFuncSuccess, InterSendOnlineFileFormat, Server} from "../static/server";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import Vue from "vue";

import AjaxActionNotForm from "../vue-component/AjaxActionNotForm.vue";
import {_Endpoint} from "../interfaces/inter-sbe";
import {log} from "util";
import {createPopper} from '@popperjs/core';

declare var jQuery: any;
declare var wp: any;

declare var pereere_dot_com_sbe_general_localize: any;
Store.initAdmin({
  serverUrl   : pereere_dot_com_sbe_general_localize.ajax_url,
  actionString: pereere_dot_com_sbe_general_localize.ajax_action
});


export const vdata = {
  // all: pereere_dot_com_sbe_general_localize.all as _AdminToc,
  localize: pereere_dot_com_sbe_general_localize,
  //
  zajax          : {
    searching: {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',
    } as _Ajax,
    default  : {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',
    } as _Ajax,
  },
  typeCounter    : 0,
  searchKeyword  : '',
  results        : [],
  rawResults     : [] as Array<_Endpoint>,
  results2       : [
    {
      name: 'The link 1 here',
      url : '',
    },
    {
      name: 'The link 2 here',
      url : '',
    },
    {
      name: 'The link 3 here',
      url : '',
    },
    {
      name: 'The link 4 here',
      url : '',
    },
    {
      name: 'The link 5 here',
      url : '',
    },
  ],
  tableData      : {
    columns          : [
      {
        label  : 'Name',
        field  : 'name',
        tooltip: 'Endpoint Name',
      },
      {
        label  : 'Endpoint',
        field  : 'endpoint',
        tooltip: 'Endpoint',
      },
      {
        label: 'Created At',
        field: 'created_at',
      },
    ],
    rows             : [],
    isLoading        : true,
    totalRecords     : 0,
    totalTrashed     : 0,
    serverParams     : {
      columnFilters: {},
      sort         : {
        created_at : '',
        modified_at: '',
      },
      page         : 1,
      perPage      : 10
    },
    paginationOptions: {
      enabled         : true,
      mode            : 'page',
      perPage         : 5,
      position        : 'bottom',
      perPageDropdown : [2, 5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 300, 400, 500, 600, 700],
      dropdownAllowAll: true,
      setCurrentPage  : 1,
      nextLabel       : 'next',
      prevLabel       : 'prev',
      rowsPerPageLabel: 'Rows per page',
      ofLabel         : 'of',
      pageLabel       : 'page', // for 'pages' mode
      allLabel        : 'All',
    },
    searchOptions    : {
      enabled       : true,
      trigger       : '', // can be "enter"
      skipDiacritics: true,
      placeholder   : 'Search links',
    },
    sortOption       : {
      enabled: false,
    },
    //
    post_status         : 'publish',
    selectedRowsToDelete: [] as Array<_Endpoint>,
    searchKeyword       : '',
  },
  resultTopOffset: 0,
  show           : false,
  //
  debug          : false,
  page           : 1,
  languageIndex  : 1,
  something_value: false,
  screen_width   : 0,
};

const mGeneral         = {
  generalInit() {
    // this.addEvents();
    jQuery('.sc-search-bar').show();
  },
  getNewAjax(): _Ajax {
    return {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',

    };
  },
};
const mComputedGeneral = {
  ajax() {
    return vdata.zajax
  },
};

const mEndpoints    = {
  startSearch() {
    const searchText   = vdata.searchKeyword.toString().trim().toLowerCase();
    const results      = vdata.rawResults.filter((e) => {
      const text = e.name.toString().trim().toLowerCase();
      return text.indexOf(searchText) > -1;
    });
    const returnResult = [];
    for (let a = 0; a < 5; a++) {
      try {
        const endpoint = results[a];
        if('url' in endpoint){
          returnResult.push(endpoint);
        }
      } catch (e) {

      }

    }
    vdata.results = returnResult;
  },
  startSearch2() {
    setTimeout(() => {
      vdis.xhrStartSearch();
    }, 200);

  },
  clearSearch() {
    vdata.searchKeyword = '';
  },

  onFucus() {
    this.showResults();
  },
  onBlur() {
    setTimeout(() => {
      this.hideResults();
    }, 200);
  },
  showResults() {
    vdata.show = true;
  },
  hideResults() {
    vdata.show = false;
  },
  addEvents() {
    window.onscroll = () => {
      vdis.hideResults();
    }
  },
  addEvents3() {
    window.onscroll = () => {
      console.log('Scrolling');
      const topOffset       = jQuery('.sbe-input-section').offset().top;
      vdata.resultTopOffset = topOffset;

      const styleId = 'sbe-style-input-section';
      const css     = `
        <style id="${styleId}">
          ul.sbe-results{
            top: ${topOffset}px;
          }
        </style>
      `;

      const popcorn = document.querySelector('.sbe-input-section');
      const tooltip = document.querySelector('.sbe-results');
      // @ts-ignore
      createPopper(popcorn, tooltip, {
        placement: 'bottom-start',
      });
    }
  },
  addEvents2() {
    window.onscroll = () => {
      console.log('Scrolling');
      const topOffset       = jQuery('.sbe-input-section').offset().top;
      vdata.resultTopOffset = topOffset;

      const styleId = 'sbe-style-input-section';
      const css     = `
        <style id="${styleId}">
          ul.sbe-results{
            top: ${topOffset}px;
          }
        </style>
      `;
      jQuery('head').find('#' + styleId).remove();
      jQuery('head').append(css);
    }
  },
  typedIn() {
    setTimeout(() => {
      vdis.startSearch();
    }, 100);
  },
  typedIn2() {
    vdata.typeCounter++;

    setTimeout(() => {
      vdata.typeCounter--;
      if (vdata.typeCounter === 0) {
        if (vdata.searchKeyword.trim().length > 0) {
          vdis.xhrStartSearch();
        }
      }
    }, 3);
  },
};
const mComEndpoints = {};

const v_method   = {...mEndpoints, ...mGeneral};
const v_computed = {...mComEndpoints, ...mComputedGeneral,};

const xhr = {
  xhrLoadEndpoints() {
    let dis                      = this;
    const handleAjax: HandleAjax = new HandleAjax(vdata.zajax.default);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          params: {
            per_page      : vdata.tableData.paginationOptions.perPage,
            page          : vdata.tableData.paginationOptions.setCurrentPage,
            search_keyword: vdata.searchKeyword,
            status        : vdata.tableData.post_status,
          },
        }
      ],
      what: "public_sbe_ajax_public_load_endpoints",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        vdata.rawResults = done.data.details.endpoints;
      },
      funcFailue(done) {
        handleAjax.error(done);
        // vdata.tableData.isLoading = false;
      },
    });
  },
  xhrStartSearch() {
    let dis                      = this;
    const handleAjax: HandleAjax = new HandleAjax(vdata.zajax.default);
    new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          params: {
            per_page      : vdata.tableData.paginationOptions.perPage,
            page          : vdata.tableData.paginationOptions.setCurrentPage,
            search_keyword: vdata.searchKeyword,
            status        : vdata.tableData.post_status,
          },
        }
      ],
      what: "public_sbe_ajax_public_search_for_keyword",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        vdata.results = done.data.details.endpoints;
      },
      funcFailue(done) {
        handleAjax.error(done);
        // vdata.tableData.isLoading = false;
      },
    });
  },
};

const m_listner = {
  listner_init() {
    let dis = this;
    jQuery(".close_times_yy").on("click", () => {
      jQuery(".close_times_yy").parents("#mp-thankyou").hide("slow");
    });
    dis.listner_to_focus();
    window.onresize = () => {
      vdata.screen_width = window.screen.width;
    }
  },
  listner_to_focus() {
    let dis = this;
    window.addEventListener("click", function (e: any) {
      // console.log('clicked', e);
      //     let isInside = document.querySelector(".mega-search-menu-categories2").contains(e.target);
      //     let isInside = document.querySelector(".mega-search-menu-categories2").contains(e.target);

    });

    window.addEventListener("click", function (e: any) {

      // try {//modal-preview-image
      //     let contains = document.querySelector("aside.product-compare-shopping-list").contains(e.target);
      //     let containsButton = document.querySelector("button.btn-info").contains(e.target);
      //     // console.log({contains, containsButton});
      //     if (!contains && !containsButton) {
      //         // jQuery('aside.product-compare-shopping-list').removeClass('show-now');
      //         jQuery('aside.product-compare-departments-lists').removeClass('show-now');
      //     }
      // } catch (e) {
      //     // console.error(e);
      // }
    });

    window.addEventListener("click", function (e: any) {
      // try {//modal-preview-image
      //     let contains = document.querySelector("aside.product-compare-shopping-list").contains(e.target);
      //     let containsButton = document.querySelector("button.btn-info").contains(e.target);
      //     let contains3 = document.querySelector("aside.product-compare-departments-list").contains(e.target);
      //     // console.log({contains, containsButton, contains3});
      //     // if (!contains && !containsButton && !contains3) {
      //     //   jQuery('aside.product-compare-shopping-list').removeClass('show-now');
      //     //   jQuery('aside.product-compare-departments-lists').removeClass('show-now');
      //     // }
      // } catch (e) {
      //     // console.error(e);
      // }
    });

  }
};

const m_init = {
  init() {
    let dis = this;
    vdis    = dis;
    vComp   = dis;
    // @ts-ignore
    this.listner_init();
    this.nn();
    this.xhrLoadEndpoints();
    this.generalInit();
  },
  pageChanged(page) {

  },
};

let vmethods = {
  ...mGeneral, ...v_method,
  blobToFile(theBlob: Blob, fileName: string): File {
    let b: any         = theBlob;
    //A Blob() is almost a File() - it's just missing the two properties below which we will add
    b.lastModifiedDate = new Date();
    b.name             = fileName;

    //Cast to a File() type
    return <File>theBlob;
  },
  initUnhide() {
    jQuery(".mpereere-vue-loading").css("display", "none");
  },
  root_get_random() {
    return (Math.random() * 9).toString().replace('.', '') + +new Date;
  },
  root_random() {
    return (Math.random() + 1).toString(36).substring(7) + new Date;
  },
  root_opposite_hex_color(hex: string) {
    hex = hex.replace('#', '');
    // console.log('', {hex, oppo});
    return '#' + (Number(`0x1${hex}`) ^ 0xFFFFFF).toString(16).substr(1).toUpperCase();
  },
  /**
   *
   * @param select_button_text
   * @param select_head_text
   * @param function_gotten
   *
   * @return [ url : 'https://imageurl', id : 'asset_id']
   */
  root_pick_image_from_media(select_button_text: any, select_head_text: any, function_gotten: any) {
    let frame = wp.media({
      title   : select_head_text,
      button  : {
        text: select_button_text
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
      function_gotten(result);
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
  },
  check_bad() {
    if (Store.isBad()) {
      this.all = [];
    }
  },
  nn() {
    if (Store.jQuery()) {
      this.all = [];
      return true;
    }
    return false;
  },
  reset() {
    this.nn();

    // jQuery('button.something_value').trigger('click');
    vdata.something_value = !vdata.something_value;
    // console.log("resetting", this.something_value);
    //    jQuery(".btn-hide-now").trigger("click");
  },
  ...xhr,
  ...m_init, ...m_listner,
};

var vdis: typeof vmethods    = null;
var vComp: typeof v_computed = null;

(function ($) {

  let elem = ".sc-search-bar";

  let exist1 = jQuery(elem).length;
  console.log({exist1}, elem);
  let loadCount = 0;

  function loadInstance1() {
    // if ('Vue' in window) {
    console.log('Vue exists 3r33');
    // let exist1 = jQuery(elem).length;
    // console.log({exist1}, elem);
    // if (exist1) {
    new Vue({
      el        : elem,
      data      : vdata,
      methods   : vmethods,
      created   : function () {
        // console.clear();
        jQuery(elem).css("display", "block");
        this.initUnhide();
        this.init();
        jQuery('.all-loading').hide();
        jQuery('.all-loaded').show();
        console.log('Created');
      },
      computed  : v_computed,
      components: {}
    });
  }

  if (exist1) {
    loadInstance1();
  } else {
    console.log({exist1});
  }

})(jQuery);






















