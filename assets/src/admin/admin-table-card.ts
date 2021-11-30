"use strict";
import {Store} from "../static/store";
import {InterFuncSuccess, InterSendOnline, InterSendOnlineFileFormat, Server} from "../static/server";
import AjaxAction from "../vue-component/AjaxAction.vue";
import LoadingButton from "../vue-component/LoadingButton.vue";
import HoverNotifications from "../vue-component/HoverNotifications.vue";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {MpSortTable} from "../static/table-sort";
import Vue from "vue";
import {_HoverNotification} from "../vue-component/enums";
import 'vue-good-table/dist/vue-good-table.css'
import {VueGoodTable} from 'vue-good-table';
import AjaxActionNotForm from "../vue-component/AjaxActionNotForm.vue";
import TimeComp from "../vue-component/TimeComp.vue";
import Cookies from 'js-cookie';
import {_Endpoint} from "../interfaces/inter-sbe";
import useTagSearch from "../composables/useTagSearch";
import Multiselect from 'vue-multiselect'
import "vue-multiselect/dist/vue-multiselect.min.css";
import {_DeckGroup} from "../interfaces/inter-sp";
import useDecks from "../composables/useDecks";
import InputEditor from "../vue-component/InputEditor.vue";

import "../../css/admin/admin-table-card.scss";
import "./install-composition-api";
import useTableCard from "../composables/useTableCard";

declare var jQuery: any;
declare var bootstrap: any;
declare var wp: any;

declare var ClassicEditor: any;
declare var pereere_dot_com_sp_general_localize_4736: any;
const localize = pereere_dot_com_sp_general_localize_4736;
Store.initAdmin({
  serverUrl   : localize.ajax_url,
  actionString: localize.ajax_action,
  nonce       : localize.nonce,
});

export const vdata = {
  // all: pereere_dot_com_sbe_general_localize.all as _AdminToc,
  localize: localize,
  //
  zajax: {
    load   : {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',

    } as _Ajax,
    update : {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',

    } as _Ajax,
    delete : {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',

    } as _Ajax,
    create : {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',

    } as _Ajax,
    overlay: {
      sending       : false,
      error         : false,
      errorMessage  : '',
      success       : false,
      successMessage: '',

    } as _Ajax,
  },
  // hoverNotifications  : [] as Array<_HoverNotification>,
  sendOnlineDeckGroups: null as Server,
  tableData           : {
    columns          : [
      {
        label  : 'Name',
        field  : 'name',
        tooltip: 'Endpoint Name',
      },
      {
        label: 'Created At',
        field: 'created_at',
      },
      {
        label: 'Updated At',
        field: 'updated_at',
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
      perPage         : Cookies.get('alfPerPage') ? Number(Cookies.get('alfPerPage')) : 2,
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
  //
  showMain       : false,
  debug          : false,
  page           : 1,
  languageIndex  : 1,
  something_value: false,
  screen_width   : 0,
};

const mGeneral         = {
  generalInit() {
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
  resetVue(){
    this.$forceUpdate();
    console.log('now reset');
  }
};
const mComputedGeneral = {
  ajax() {
    return vdata.zajax
  },
};

const mCard    = {
  createDeckGroup() {
    this.newDeckGroup.xhrCreateNewDeckGroup();
  },
};
const mComCard = {
  tableDataValue() {
    return dis(this).decks.tableData.value;
  },
  deckToEdit() {
    return dis(this).decks.itemToEdit.value;
  },
  tableCardItem() {
    return dis(this).useTableCard.items.value;
  },
  tableCardGroup() {
    return dis(this).useTableCard.cardGroup.value;
  },
};

const v_method   = {...mCard, ...mGeneral};
const v_computed = {...mComCard, ...mComputedGeneral,};

const xhr = {
  xhrUpdateEndpoint(endpoint: _Endpoint) {
    let dis                      = this;
    const handleAjax: HandleAjax = new HandleAjax(vdis.getNewAjax());
    vdata.sendOnlineDeckGroups   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          endpoint: endpoint,
        }
      ],
      what: "admin_sbe_ajax_admin_update_endpoint",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
      },
      funcFailue(done) {
        handleAjax.error(done);
        // vdata.tableData.isLoading = false;
      },
    });
  },
  xhrBatchDeleteEndpoint() {
    if (!confirm("Are you sure you want to delete this endpoint?")) {
      return false;
    }
    let dis                      = this;
    const handleAjax: HandleAjax = new HandleAjax(vdata.zajax.delete);
    vdata.sendOnlineDeckGroups   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          // endpoints: vdata.tableData.selectedRowsToDelete,
        }
      ],
      what: "admin_sbe_ajax_admin_batch_delete_endpoint",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        // vdis.xhrLoadEndpoints();
      },
      funcFailue(done) {
        handleAjax.error(done);
        // vdata.tableData.isLoading = false;
      },
    });
  },
  xhrCreateEndpoint() {
    let dis                      = this;
    const handleAjax: HandleAjax = new HandleAjax(vdata.zajax.create);
    vdata.sendOnlineDeckGroups   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          // name    : vdata.vEndpoints.create.name,
          // endpoint: vdata.vEndpoints.create.endpoint,
        }
      ],
      what: "admin_sbe_ajax_admin_create_endpoint",
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
  },
};

const m_listner = {
  listner_init() {
    let dis = this;
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

    // Hide table action when not clicked on td
    window.addEventListener("click", function (e: any) {
      try {
        let contains = document.querySelector(".sp-boxes").contains(e.target);
        if (!contains) {
          jQuery('.table-action').hide();
        }
      } catch (e) {
        // console.error(e);
      }
    });

  }
};

const m_init = {
  init() {
    vdis  = this;
    vComp = this;
    // @ts-ignore
    this.listner_init();
    this.nn();
    this.generalInit();
    dis(this).useTableCard._load().then(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-loaded').show();
      vdata.showMain = true;
    }).catch(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-error').show();
    });

    // vdis.xhrLoadEndpoints();
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
  rootNewTable(details, perPage) {
    const dis = this;  //eslint-disable-line
    return new MpSortTable({
      vue          : dis,
      all          : details,
      allToUse     : details,
      holdDisplay  : [],
      perPage      : perPage,
      pageNow      : 0,
      sortAscended : [],
      pagination222: [],
      pageNow222   : 0,
      searchPage   : "this",
      search       : "",
      pagination   : [],
      pageStart    : 0,
      pageStop     : 0, // set on init to per_page
      disableRight : false,
      disableLeft  : false,
      dis_class    : null,
      showing_text : "",
      filterParams : []
    })
  },
  root_wp_editor_get_value(tetarea_id) {
    return wp.editor.getContent(tetarea_id)
  },
  root_wp_editor_get_value2(tetarea_id) {
    // @ts-ignore
    return jQuery('#' + tetarea_id + '_ifr').contents().find("body").html();
    // return wp.editor.getContent(tetarea_id)
  },
  root_remove_wp_editor(tetarea_id) {
    wp.editor.remove(tetarea_id);
  },
  root_opposite_hex_color(hex: string) {
    hex = hex.replace('#', '');
    // console.log('', {hex, oppo});
    return '#' + (Number(`0x1${hex}`) ^ 0xFFFFFF).toString(16).substr(1).toUpperCase();
  },
  root_add_wp_editor(tetarea_id, add_media = true) {
    wp.editor.initialize(tetarea_id, {
      mediaButtons: add_media,
      tinymce     : {
        wpautop : true,
        plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
        toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
        toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
      },
      quicktags   : true,
    });
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
  getAnimation(num) {
    if ((num % 2) === 0) {
      return "animated slideInLeft";
    } else {
      return "animated slideInRight";
    }
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

  /**
   *
   * @param element
   * @param initial_value
   * @param function_keyup
   * @param success_callback
   * @param error_callback
   */
  setCkeditor(element: HTMLElement, initial_value: string, function_keyup: Function, success_callback: Function = null, error_callback: Function = null) {
    ClassicEditor.create(element, {
      alignment: {
        options: ['left', 'right']
      },
      toolbar  : [
        "undo", "redo", "bold", "italic", "blockQuote", "ckfinder", "imageTextAlternative", "imageUpload", "heading", "imageStyle:full", "imageStyle:side", "indent", "outdent", "link", "numberedList", "bulletedList", "mediaEmbed", "insertTable", "tableColumn", "tableRow", "mergeTableCells"
      ]
    }).then(editor => {
      editor.setData(initial_value);
      editor.editing.view.document.on('keydown', (evt, data) => {
        function_keyup(editor);
      });
    }).catch(error => {
      console.error(error);
    });
  },
  change_page(page) {
    let dis  = this;
    dis.page = page;
    this.pageChanged(page);
  },
  /**
   * Set Ck Editor
   *
   * @param  selector String e.g. .ck-new-textarea
   * @param func_keyup Call back function(text)
   */
  set_ck(selector, func_keyup) {
    setTimeout(() => {
      ClassicEditor
        .create(document.querySelector(selector), {}).then(editor => {
        editor.editing.view.document.on("keyup", function (event, data) {
          let gt = editor.getData();

          func_keyup(gt);
        });
      }).catch(error => {
        console.log(error);
      });
    }, 10);
  },

  ...xhr,
  ...m_init, ...m_listner,

};

var vdis: typeof vmethods    = null;
var vComp: typeof v_computed = null;

function setup(props) {
  const url          = new URL(window.location.href);
  const searchParams = new URLSearchParams(url.search);
  const status       = searchParams.get('status');
  const action       = searchParams.get('action');
  const cardGroupId  = Number(searchParams.get('card-group'));

  // console.log('in setup', {url, searchParams, status, cardGroupId});
  return {
    decks       : useDecks(status),
    searchTags  : useTagSearch(),
    useTableCard: useTableCard(cardGroupId),
  };
}

function dis(context): ReturnType<typeof setup> {
  return context;
}

(function () {

  let elem = ".admin-table-card";

  let exist1 = jQuery(elem).length;
  console.log({exist1}, elem);
  let loadCount = 0;

  function loadInstance1() {
    new Vue({
      el        : elem,
      data      : vdata,
      methods   : vmethods,
      setup,
      created   : function () {
        // console.clear();
        jQuery(elem).css("display", "block");
        this.initUnhide();
        this.init();

        console.log('Created');
      },
      computed  : v_computed,
      components: {
        InputEditor,
        // 'input-editor': InputCk,
        TimeComp,
        'ajax-action': AjaxAction,
        // 'ajax-action-not-form': AjaxActionNotForm,
        LoadingButton,
        HoverNotifications,
        // VueTableDynamic,
        VueGoodTable,
        'vue-mulitiselect': Multiselect,
      },
    });
  }

  if (exist1) {
    loadInstance1();
  } else {
    console.log({exist1});
  }

})();






















