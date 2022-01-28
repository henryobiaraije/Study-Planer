import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {ref, onMounted, computed} from "@vue/composition-api";
import {_Card, _Deck, _DeckGroup, _Study, _Tag} from "../interfaces/inter-sp";
import {Store} from "../static/store";
import Vue from "vue";
import useImageCard from "./useImageCard";

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
  let _modalOpenQuestion    = ref(null);
  let allQuestions          = ref<Array<_Card>>([]);
  let currentQuestionIndex  = ref<number>(-1);
  let currentQuestion       = ref<_Card>(null);
  let showCurrentAnswer     = ref<boolean>(false);
  let showGrade             = ref<boolean>(false);
  let lastAnsweredDebugData = ref<Array<{ [key: string]: string }>>(null);
  let studyLogIntervalId    = ref(null);

  /**
   * Returns the study belonging to a deck or return a new study
   * @param deck
   */
  const load                    = () => {
    return xhrLoad();
  }
  const openStudyModal          = (deck: _Deck) => {
    studyToEdit.value  = getStudyForDeck(deck);
    const modalElement = jQuery('#modal-new')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.show();
    modalElement.addEventListener('shown.bs.modal', function () {
      jQuery('body').append(jQuery(modalElement).parent());
    });
    modalElement.addEventListener('hidden.bs.modal', function () {

    });
  };
  const closeStudyModal         = () => {
    jQuery('#hide-modal-new').trigger('click');
  };
  const openQuestionModal       = () => {
    const modalElement       = jQuery('#modal-questions')[0];
    const myModal            = new bootstrap.Modal(modalElement);
    _modalOpenQuestion.value = myModal;
    myModal.show();
    modalElement.addEventListener('shown.bs.modal', function () {
      jQuery('body').append(jQuery(modalElement).parent());
      // studyLogIntervalId.value = setInterval(() => {
      //
      // }, 5000);
    });
    modalElement.addEventListener('hidden.bs.modal', function () {
      console.log('close question modal');
      // if (currentQuestion.value !== null) {
      //     xhrRecordStudyLog(studyToEdit.value, currentQuestion.value, 'stop');
      // }
      xhrGetSingleDeckGroup(currentQuestion.value.card_group.deck.id)
    });
  };
  const closeQuestionModal      = () => {
    // const modalElement = jQuery('#modal-questions')[0];
    // const myModal = new bootstrap.Modal(modalElement);
    // _modalOpenQuestion.value.hide();
    jQuery('#hide-question-moadl').trigger('click');
    // jQuery('#modal-questions').hide();
  };
  const openStudyCompleteModal  = (show = true) => {
    const modalElement = jQuery('#modal-study-complete')[0];
    const myModal      = new bootstrap.Modal(modalElement);
    if (show) {
      myModal.show();
      modalElement.addEventListener('shown.bs.modal', function () {
        jQuery('body').append(jQuery(modalElement).parent());
      });
      modalElement.addEventListener('hidden.bs.modal', function () {
      });
    }
    return myModal;
  };
  const closeStudyCompleteModal = () => {
    openStudyCompleteModal().hide(false);
  };
  const startStudy              = () => {
    xhrCreateOrUpdateStudy(studyToEdit.value).then(() => {
      closeStudyModal();
      openQuestionModal();
      return xhrGetTodayQuestionsInStudy(studyToEdit.value);
    }).then((res) => {
      console.log('resolved qqq');
      currentQuestionIndex.value = -1;
      _nextQuestion();
    });
  }
  const _nextQuestion           = () => {
    // if (currentQuestion.value !== null) {
    //     xhrRecordStudyLog(studyToEdit.value, currentQuestion.value, 'stop');
    // }
    const hasMoreQuestions = currentQuestionIndex.value + 1 < (allQuestions.value.length);
    if (hasMoreQuestions) {
      if (allQuestions.value.length > 0) {
        currentQuestionIndex.value++;
        currentQuestion.value = allQuestions.value[currentQuestionIndex.value];
        console.log('Next Question');
        xhrRecordStudyLog(studyToEdit.value, currentQuestion.value, 'start');
        if (currentQuestion.value.card_group.card_type === 'image') {
          useImageCard().applyPreviewCss(currentQuestion.value.question);
          useImageCard().applyPreviewCss(currentQuestion.value.answer);
          // useImageCard().applyPreviewCssOld(currentQuestion.value.old_question);
          // useImageCard().applyPreviewCssOld(currentQuestion.value.old_question);
          useImageCard().applyPreviewCssOld(currentQuestion.value.old_answer);

          useImageCard().applyBoxesPreviewCss(currentQuestion.value.question.boxes);
          useImageCard().applyBoxesPreviewCss(currentQuestion.value.answer.boxes);
          useImageCard().applyBoxesPreviewCss(currentQuestion.value.old_question.boxes);
          useImageCard().applyBoxesPreviewCss(currentQuestion.value.old_answer.boxes);
          useImageCard().applyBoxesPreviewCssOld(currentQuestion.value.old_answer.boxes);
        }
      }
    } else {
      // currentQuestionIndex.value = -1;
      // currentQuestion.value = null;
      // showCurrentAnswer.value = false;
      // showGrade.value = false;
      closeQuestionModal();
      openStudyCompleteModal(true);
    }

  }
  const _getQuestions           = () => {
    // xhrGetTodayQuestionsInStudy(studyToEdit.value);
  }
  const getStudyForDeck         = (deck: _Deck) => {
    let study = studies.value.find((s: _Study) => deck.id === s.deck.id);
    if (undefined === study) {
      study = {
        deck             : deck,
        tags             : [],
        tags_excluded    : [],
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
  const _showAnswer             = () => {
    showCurrentAnswer.value = true;
    showGrade.value         = true;
    // console.log('show curr', showCurrentAnswer.value, showGrade.value);
  }
  const _markAnswer             = (grade: string) => {
    xhrMarkAnswer(
      studyToEdit.value,
      currentQuestion.value,
      grade,
      currentQuestion.value.answer,
      currentQuestion.value.question
    );
    setTimeout(() => {
      showCurrentAnswer.value = false;
      showGrade.value         = false;
      if ('again' === grade) {
        // Answer it again
        currentQuestion.value.answering_type = 'Revising Card'
        allQuestions.value.push(currentQuestion.value);
      }
      _nextQuestion();
    }, 200);
  }
  const _hold                   = () => {
    xhrMarkAnswerOnHold(studyToEdit.value, currentQuestion.value);
    setTimeout(() => {
      showCurrentAnswer.value = false;
      showGrade.value         = false;
      _nextQuestion();
    }, 200);
  }
  const _acceptChanges          = (button: string) => {
    // xhrAcceptAnswer(button, studyToEdit.value, currentQuestion.value).then(() => {
    //
    // });
    if ('yes' === button) {
      // currentQuestion.value.
    } else if ('no' === button) {
      currentQuestion.value.question = currentQuestion.value.old_question;
      currentQuestion.value.answer   = currentQuestion.value.old_answer;
    } else {
      _hold();
      _nextQuestion();
    }
    currentQuestion.value.has_updated = false;
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
        what: "front_sp_ajax_front_get_deck_groups",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          const groups     = done.data.details.deck_groups;
          const allStudies = done.data.studies.studies;
          // console.log({groups, allStudies});
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
  const xhrRecordStudyLog           = (study: _Study, card: _Card, action = 'start') => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study_id: study.id,
            card_id : card.id,
            action  : action,
          }
        ],
        what: "front_sp_ajax_front_record_study_log",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          // console.log(done);
          // allQuestions.value = done.data.user_cards.cards;
          // studyToEdit.value = done.data;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.stop();
          reject();
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
        what: "front_sp_ajax_front_get_today_questions_in_study",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          console.log(done);
          allQuestions.value = done.data.user_cards.cards;
          // studyToEdit.value = done.data;
          console.log('Resolving qqq');
          resolve(1);
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
        what: "front_sp_ajax_front_create_study",
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
  const xhrMarkAnswer               = (study: _Study, card: _Card, grade: string, answer: any, question: any) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study_id  : study.id,
            grade,
            card_id   : card.id,
            answer,
            question,
            card_whole: card,
          }
        ],
        what: "front_sp_ajax_front_mark_answer",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          lastAnsweredDebugData.value = done.data.debug_display;
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
  const ____xhrAcceptAnswer         = (button: string, study: _Study, currentQuestion: _Card) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            button,
            study,
            currentQuestion
          }
        ],
        what: "front_sp_ajax_front_accept_changes",
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
  const xhrMarkAnswerOnHold         = (study: _Study, card: _Card) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            study_id: study.id,
            card_id : card.id,
          }
        ],
        what: "front_sp_ajax_front_mark_answer_on_hold",
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
  const xhrMarkAnswerOnHold2        = (study: _Study, card: _Card, grade: string, answer: string) => {
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
        what: "front_sp_ajax_front_mark_answer_on_hold",
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
  const xhrGetSingleDeckGroup       = (deckId: number) => {
    const dis                    = this;
    const handleAjax: HandleAjax = new HandleAjax(ajaxSaveStudy.value);
    return new Promise((resolve, reject) => {
      sendOnline = new Server().send_online({
        data: [
          Store.nonce,
          {
            deck_id: deckId,
          }
        ],
        what: "front_sp_ajax_front_get_single_deck_group",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          const _deckGroup: _DeckGroup = done.data;

          const index = deckGroups.value.findIndex(d => d.id === _deckGroup.id);

          if (index > -1) {
            deckGroups.value[index] = _deckGroup;
          }
          console.log({_deckGroup, index});
          jQuery('.reset-vue').trigger('click');
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
    showCurrentAnswer, showGrade, _hold, _acceptChanges,
    _showAnswer, _markAnswer, lastAnsweredDebugData,
  };

}