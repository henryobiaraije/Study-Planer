import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {ref, onMounted} from "@vue/composition-api";
import {_Deck, _DeckGroup, _Study, _Tag} from "../interfaces/inter-sp";
import {Store} from "../static/store";

declare var bootstrap;

export default function (status = 'publish') {
  const ajax          = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxSaveStudy = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  let sendOnline      = null;
  let deckGroups      = ref<Array<_DeckGroup>>([]);
  let studies         = ref<Array<_Study>>([]);
  let studyToEdit     = ref<_Study>(null);
  let studyToEdit2    = ref<_Study>({
    deck             : null,
    no_of_new        : 0,
    study_all_new    : true,
    tags             : [],
    all_tags         : true,
    no_to_revise     : null,
    study_all_on_hold: true,
    no_on_hold       : null,
    revise_all       : true,
    user             : null,
  });

  /**
   * Returns the study belonging to a deck or return a new study
   * @param deck
   */
  const getStudyForDeck = (deck: _Deck) => {
    let study = studies.value.find((s: _Study) => deck.id === s.deck.id);
    if (undefined === study) {
      study = {
        deck             : deck,
        tags             : [],
        all_tags         : true,
        no_of_new        : '' as any as number,
        no_on_hold       : '' as any as number,
        no_to_revise     : '' as any as number,
        revise_all       : true,
        study_all_new    : true,
        study_all_on_hold: true,
        user             : null,
      };
    }
    console.log({study});
    return study;
  }

  //
  const load            = () => {
    return xhrLoad();
  }
  const openStudyModal  = (deck: _Deck) => {
    studyToEdit.value  = getStudyForDeck(deck);
    const modalElement = jQuery('#modal-new')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.show();
    modalElement.addEventListener('shown.bs.modal', function () {

    });
    modalElement.addEventListener('hidden.bs.modal', function () {

    });
  };
  const closeStudyModal = () => {
    const modalElement = jQuery('#modal-new')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.hide();
  };
  const startStudy      = () => {
    xhrCreateStudy(studyToEdit.value);
  }
  //

  const xhrLoad        = () => {
    console.log('start loading');
    const handleAjax: HandleAjax = new HandleAjax(ajax.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            params: {
              per_page      : 1000,
              page          : 1,
              search_keyword: '',
              status        : 'publish',
            },
          }
        ],
        what: "admin_sp_ajax_front_get_deck_groups",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          const groups     = done.data.details.deck_groups;
          const allStudies = done.data.studies.studies;
          console.log({groups, allStudies});
          deckGroups.value = groups;
          studies.value    = allStudies;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.error(done);
        },
      });
    });
  };
  const xhrCreateStudy = (study: _Study) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study: study
          }
        ],
        what: "admin_sp_ajax_front_create_study",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          studyToEdit.value = done.data;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.error(done);
        },
      });
    });
  };


  return {
    ajax, ajaxSaveStudy,
    deckGroups, studyToEdit, startStudy,
    load, openStudyModal, closeStudyModal,
  };

}