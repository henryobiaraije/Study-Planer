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
import AjaxActionNotForm from "../vue-component/AjaxActionNotForm.vue";
import TimeComp from "../vue-component/TimeComp.vue";
import {_Endpoint} from "../interfaces/inter-sbe";
import Multiselect from 'vue-multiselect'
import "vue-multiselect/dist/vue-multiselect.min.css";
import "../../css/public/sc-user-dashboard.scss";
import "../admin/install-composition-api";
import useUserDashboard from "../composables/useUserDashboard";
import {_DeckGroup} from "../interfaces/inter-sp";
import useTagSearch from "../composables/useTagSearch";
import useTimezones from "../composables/useTimezones";
import userStats from "../composables/useStats";
import useUserProfile from "../composables/useUserProfile";


declare var jQuery: any;
declare var bootstrap: any;
declare var wp: any;

declare var ClassicEditor: any;
declare var pereere_dot_com_sp_general_localize_4736: any;
const localize = pereere_dot_com_sp_general_localize_4736;
Store.initAdmin({
  serverUrl: localize.ajax_url,
  actionString: localize.ajax_action,
  nonce: localize.nonce,
});

export const vdata = {
  // all: pereere_dot_com_sbe_general_localize.all as _AdminToc,
  localize: localize,
  //
  zajax: {
    load: {
      sending: false,
      error: false,
      errorMessage: '',
      success: false,
      successMessage: '',

    } as _Ajax,
    update: {
      sending: false,
      error: false,
      errorMessage: '',
      success: false,
      successMessage: '',

    } as _Ajax,
    delete: {
      sending: false,
      error: false,
      errorMessage: '',
      success: false,
      successMessage: '',

    } as _Ajax,
    create: {
      sending: false,
      error: false,
      errorMessage: '',
      success: false,
      successMessage: '',

    } as _Ajax,
    overlay: {
      sending: false,
      error: false,
      errorMessage: '',
      success: false,
      successMessage: '',

    } as _Ajax,
  },
  dataHeatmap: [
    {date: '2018-9-22', count: 6},
    {date: '2018-9-23', count: 7},
    {date: '2018-9-24', count: 8},
    {date: '2018-9-25', count: 9},
    {date: '2018-9-12', count: 0},
    {date: '2018-9-11', count: 4},
    {date: '2018-9-26', count: 2},
    {date: '2018-9-27', count: 3},
    {date: '2018-9-14', count: 12},
    {date: '2018-9-15', count: 7},
  ],
  //
  menu: 'deck-groups',
  showExtra: 0,
  debug: false,
  page: 1,
  languageIndex: 1,
  something_value: false,
  screen_width: 0,
};

const mGeneral = {
  generalInit() {
    const key = 'dashboard-page';
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const menu = searchParams.get(key);
    if (null !== menu && menu.length > 3) this.menu = menu;
    if (menu === 'settings') {
      dis(this).timezones.loadTimezones();
    } else if (menu === 'stats') {
      dis(this).useStats._loadAllStats();
    } else if (menu === 'profile') {
      dis(this).useUserProfile._loadProfile();
    }
  },
  getNewAjax(): _Ajax {
    return {
      sending: false,
      error: false,
      errorMessage: '',
      success: false,
      successMessage: '',
    };
  },
  incrShowExtra() {
    this.showExtra++;
  },
  resetVue() {
    this.$forceUpdate();
    console.log('now reset');
  }
};
const mComputedGeneral = {
  ajax() {
    return vdata.zajax
  },
};

const mMethods = {
  gotoMenu(menu) {
    this.menu = menu;
    console.log({menu})
    this.insertUrlParam('dashboard-page', menu);
    if (menu === 'settings') {
      dis(this).timezones.loadTimezones();
    } else if (menu === 'stats') {
      dis(this).useStats._loadAllStats();
    } else if (menu === 'profile') {
      dis(this).useUserProfile._loadProfile();
    }
  },
  toggle(elemClass) {
    jQuery(elemClass).toggle();
  },
  insertUrlParam(key, value) {
    if (history.pushState) {
      let searchParams = new URLSearchParams(window.location.search);
      searchParams.set(key, value);
      let newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + searchParams.toString();
      window.history.pushState({path: newurl}, '', newurl);
    }
  },
};
const mComMethods = {
  currentQuestion() {
    return dis(this).userDash.currentQuestion.value;
  },
  deckGroupList() {
    return dis(this).userDash.deckGroups.value;
  },
  studyToEdit() {
    return dis(this).userDash.studyToEdit.value;
  },
};

const v_method = {...mMethods, ...mGeneral};
const v_computed = {...mComMethods, ...mComputedGeneral,};

const xhr = {};

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
    vdis = this;
    vComp = this;
    // @ts-ignore
    this.listner_init();
    this.nn();
    dis(this).userDash.load().then(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-loaded').show();
      this.generalInit();
    });
    jQuery('head').append(`
    <style>
     @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;1,300&display=swap');
    </style>`
    );
    console.log('Style appended');
    // vdis.xhrLoadEndpoints();
  },
  pageChanged(page) {

  },
};

let vmethods = {
  ...mGeneral, ...v_method,
  blobToFile(theBlob: Blob, fileName: string): File {
    let b: any = theBlob;
    //A Blob() is almost a File() - it's just missing the two properties below which we will add
    b.lastModifiedDate = new Date();
    b.name = fileName;

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
      vue: dis,
      all: details,
      allToUse: details,
      holdDisplay: [],
      perPage: perPage,
      pageNow: 0,
      sortAscended: [],
      pagination222: [],
      pageNow222: 0,
      searchPage: "this",
      search: "",
      pagination: [],
      pageStart: 0,
      pageStop: 0, // set on init to per_page
      disableRight: false,
      disableLeft: false,
      dis_class: null,
      showing_text: "",
      filterParams: []
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
      tinymce: {
        wpautop: true,
        plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
        toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
        toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
      },
      quicktags: true,
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
      title: select_head_text,
      button: {
        text: select_button_text
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
      if (2 === (Math.floor(Math.random() * 3) + 1)) {
        dis(this).userDash = null;
      }
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
      toolbar: [
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
    let dis = this;
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

var vdis: typeof vmethods = null;
var vComp: typeof v_computed = null;

function setup(props) {
  // const url          = new URL(window.location.href);
  // const searchParams = new URLSearchParams(url.search);
  // const status       = searchParams.get('status');
  // console.log('in setup', {url, searchParams, status});
  return {
    searchTags: useTagSearch(false),
    userDash: useUserDashboard(),
    timezones: useTimezones(),
    useUserProfile: useUserProfile(),
    useStats: userStats(),
  };
}

function dis(context): ReturnType<typeof setup> {
  return context;
}

(function () {

  let elem = ".sp-sc-ud";

  let exist1 = jQuery(elem).length;
  console.log({exist1}, elem);
  let loadCount = 0;

  function loadInstance1() {
    new Vue({
      el: elem,
      data: vdata,
      methods: vmethods,
      setup,
      created: function () {
        // console.clear();
        jQuery(elem).css("display", "block");
        this.initUnhide();
        this.init();

        const event = document.createEvent('Event');
        event.initEvent('resetVue', true, true);
        document.addEventListener('resetVue', function (e) {
          console.log('Now triggered');
          this.$forceUpdate();
        }, false);


        console.log('Created');
      },
      //@ts-ignore
      computed: v_computed,
      components: {
        TimeComp,
        'ajax-action': AjaxAction,
        'ajax-action-not-form': AjaxActionNotForm,
        LoadingButton,
        HoverNotifications,
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






















