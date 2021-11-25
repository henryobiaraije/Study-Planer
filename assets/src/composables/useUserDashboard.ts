import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {ref, onMounted, computed} from "@vue/composition-api";
import {_Card, _Deck, _DeckGroup, _Study, _Tag} from "../interfaces/inter-sp";
import {Store} from "../static/store";

declare var bootstrap;

export default function (status = 'publish') {
  const ajax                = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxSaveStudy       = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxLoadingCard     = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  let sendOnline            = null;
  let deckGroups            = ref<Array<_DeckGroup>>([]);
  let studies               = ref<Array<_Study>>([]);
  let studyToEdit           = ref<_Study>(null);
  let allQuestions          = ref<Array<_Card>>([]);
  let currentQuestionIndex  = ref<number>(-1);
  let currentQuestion       = ref<_Card>(null);
  let showCurrentAnswer     = ref<boolean>(false);
  let showGrade             = ref<boolean>(false);
  let lastAnsweredDebugData = ref<Array<{ [key: string]: string }>>(null);

  /**
   * Returns the study belonging to a deck or return a new study
   * @param deck
   */
  const load               = () => {
    return xhrLoad();
  }
  const openStudyModal     = (deck: _Deck) => {
    studyToEdit.value  = getStudyForDeck(deck);
    const modalElement = jQuery('#modal-new')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.show();
    modalElement.addEventListener('shown.bs.modal', function () {

    });
    modalElement.addEventListener('hidden.bs.modal', function () {

    });
  };
  const closeStudyModal    = () => {
    jQuery('#hide-modal-new').trigger('click');
  };
  const openQuestionModal  = () => {
    const modalElement = jQuery('#modal-questions')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.show();
    modalElement.addEventListener('shown.bs.modal', function () {

    });
    modalElement.addEventListener('hidden.bs.modal', function () {

    });
  };
  const closeQuestionModal = () => {
    const modalElement = jQuery('#modal-questions')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.hide();
  };
  const startStudy         = () => {
    xhrCreateOrUpdateStudy(studyToEdit.value).then(() => {
      closeStudyModal();
      openQuestionModal();
      return xhrGetTodayQuestionsInStudy(studyToEdit.value);
    }).then((res) => {
      currentQuestionIndex.value = -1;
      _nextQuestion();
    });
  }
  const _nextQuestion      = () => {
    currentQuestionIndex.value++;
    if (currentQuestionIndex.value < (allQuestions.value.length)) {
      if (allQuestions.value.length > 0) {
        currentQuestion.value = allQuestions.value[currentQuestionIndex.value];
      }
    }
  }
  const _getQuestions      = () => {
    // xhrGetTodayQuestionsInStudy(studyToEdit.value);
  }
  const getStudyForDeck    = (deck: _Deck) => {
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
  const _showAnswer        = () => {
    showCurrentAnswer.value = true;
    showGrade.value         = true;
    // console.log('show curr', showCurrentAnswer.value, showGrade.value);
  }
  const _markAnswer        = (grade: string) => {
    xhrMarkAnswer(studyToEdit.value, currentQuestion.value, grade, currentQuestion.value.answer);
    setTimeout(() => {
      showCurrentAnswer.value = false;
      showGrade.value         = false;
      _nextQuestion();
    }, 200);
  }
  const _hold              = (grade: string) => {
    xhrMarkAnswerOnHold(studyToEdit.value, currentQuestion.value, grade, currentQuestion.value.answer);
    setTimeout(() => {
      showCurrentAnswer.value = false;
      showGrade.value         = false;
      _nextQuestion();
    }, 200);
  }

  //

  const xhrLoad                     = () => {
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
  const xhrGetTodayQuestionsInStudy = (study: _Study) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study: study
          }
        ],
        what: "admin_sp_ajax_front_get_today_questions_in_study",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          console.log(done);
          allQuestions.value  = done.data.user_cards.cards;
          // studyToEdit.value = done.data;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.error(done);
          reject();
        },
      });
    });
  };
  const xhrCreateOrUpdateStudy      = (study: _Study) => {
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
          reject();
        },
      });
    });
  };
  const xhrMarkAnswer               = (study: _Study, card: _Card, grade: string, answer: string) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study_id: study.id,
            grade,
            card_id : card.id,
            answer
          }
        ],
        what: "admin_sp_ajax_front_mark_answer",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          lastAnsweredDebugData.value = done.data.debug_display;
          const nextInterval: number  = done.data.next_interval;
          if (1 > nextInterval) {
            allQuestions.value.push(card);
          }
          // studyToEdit.value = done.data;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.error(done);
          reject();
        },
      });
    });
  };
  const xhrMarkAnswerOnHold         = (study: _Study, card: _Card, grade: string, answer: string) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study_id: study.id,
            grade,
            card_id : card.id,
            answer
          }
        ],
        what: "admin_sp_ajax_front_mark_answer_on_hold",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          // lastAnsweredDebugData.value = done.data.debug_display;
          // const nextInterval: number  = done.data.next_interval;
          // if (1 > nextInterval) {
          //   allQuestions.value.push(card);
          // }
          // studyToEdit.value = done.data;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.error(done);
          reject();
        },
      });
    });
  };

  const answeredCount = computed<string>(() => {
    return `${currentQuestionIndex.value + 1} / ${allQuestions.value.length} `;
  })

  return {
    ajax, ajaxSaveStudy, ajaxLoadingCard,
    deckGroups, studyToEdit, startStudy, _getQuestions,
    load, openStudyModal, closeStudyModal, allQuestions,
    currentQuestion, answeredCount,
    showCurrentAnswer, showGrade, _hold,
    _showAnswer, _markAnswer, lastAnsweredDebugData,
  };

}