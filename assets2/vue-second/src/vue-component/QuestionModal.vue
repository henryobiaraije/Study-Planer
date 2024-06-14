<template>
  <div v-if="null !== oneCard">
    <!--  <div class="sp sp-modal">-->
    <v-progress-linear :model-value="percentComplete" :height="5" color="primary"></v-progress-linear>
    <div v-if="true === hasUpdated" class="chose-updated-or-old-card flex flex-col gap-3">
      <div class="info text-center py-3">This card has been updated since the last time you answered it.</div>
      <v-card>
        <v-tabs
            v-model="tab"
            align-tabs="center"
            color="deep-purple-accent-4"
        >
          <v-tab :value="1">New Card</v-tab>
          <v-tab :value="2">Old Card</v-tab>
        </v-tabs>
      </v-card>
      <div class="py-4 text-center">
        <button
            @click="acceptUpdatedCard"
            class="py-2 px-8 text-base font-bold rounded-md bg-gray-100 border border-solid border-gray-300 hover:bg-gray-300 ">
          Use
          this version
        </button>
      </div>
    </div>
    <div class="admin-image-card">
      <form @submit.prevent="" class="modal-content min-w-[85vw]" style="height: 100%;">
        <div class="mb-4">
          <!--              <?php \StudyPlannerPro\load_template('shortcodes/dashboard/template-part-accept-changes'); ?>-->
        </div>
        <div v-if="(null !== currentCard) && (undefined !== currentCard) && (!currentCard.has_updated)"
             class="sp-question min-h-[65vh] flex align-items-center overflow-x-auto moxal-y-hidden"
             style="background-repeat: no-repeat;background-size: cover;"
             :style="{'background-image' : 'url('+currentCard?.card_group?.bg_image_url+')'}">
          <div v-if="!allAnswered" class="flex flex-col justify-between gap-2 w-full">

            <div>
              <!--          If not updated -->
              <div v-if="false  === hasUpdated ">
                <QuestionDisplay
                    :show-answer="_showAnswer"
                    :one-card="oneCard"
                    :show-only-answers="showOnlyAnswers"
                    :show-current-answer="showCurrentAnswer"
                    :current-card="currentCard"
                />
              </div>

              <div v-if="true === hasUpdated && true === tabShow">
                <div v-if="1 === tab && oneCard">
                  <QuestionDisplay
                      :show-answer="_showAnswer"
                      :one-card="oneCard"
                      :show-only-answers="showOnlyAnswers"
                      :show-current-answer="showCurrentAnswer"
                      :current-card="currentCard"
                  />
                </div>
                <div v-if="2 === tab && null !== oneCardUpdated">
                  <QuestionDisplay
                      :show-answer="_showAnswer"
                      :one-card="oneCardUpdated"
                      :show-only-answers="showOnlyAnswers"
                      :show-current-answer="showCurrentAnswer"
                      :current-card="oneCardUpdated"
                  />
                </div>
              </div>
            </div>

            <div class="lower-section">

              <!-- <editor-fold desc="Prev & Next"> -->
              <div v-if="'preview' === purpose" class="">
                <div class="flex justify-space-around flex-wrap lg:flex-nowrap align-center py-4">
              <span class="flex-1 text-center">
                <v-btn
                    :disabled="index === 0"
                    color="primary"
                    @click="prev()"
                >
                <v-icon left>
                  mdi-chevron-left
                </v-icon>
                Prev
              </v-btn>
              </span>
                  <div class="flex-1 hidden lg:flex gap-4 justify-center items-center">
                    <span class="text-xl font-semibold">{{ index + 1 }}/{{ cardsList.length }}</span>
                    <span v-if="inAddQuestions" class="flex-initial">
                  <v-switch
                      v-model="userCards.form.value.selectedCards"
                      color="primary"
                      hide-details
                      :value="cardsList[index].card_group"
                      label="Selected"
                  ></v-switch>
                </span>
                  </div>
                  <span class="flex-1 text-center">
                <v-btn
                    :disabled="index === (cardsList.length - 1)"
                    color="primary"
                    @click="next()"
                >
                Next
                <v-icon left>
                  mdi-chevron-right
                </v-icon>
              </v-btn>
              </span>
                </div>
                <div class="flex-1 flex lg:hidden gap-4 justify-center items-center">
                  <span class="text-xl font-semibold">{{ index + 1 }}/{{ cardsList.length }}</span>
                  <span v-if="inAddQuestions" class="flex-initial">
                  <v-switch
                      v-model="userCards.form.value.selectedCards"
                      color="primary"
                      hide-details
                      :value="cardsList[index].card_group"
                      label="Selected"
                  ></v-switch>
                </span>
                </div>
                <p class="flex-1 w-full py-4 text-center text-base text-gray-500">You can use left and right arrow keys
                  to
                  move through the cards</p>
              </div>
              <!-- </editor-fold desc="Prev & Next"> -->

              <!-- <editor-fold desc="Good, Hard, e.t.c buttons"> -->
              <template v-if="'study' === purpose && false === hasUpdated">
                <p class="text-xl font-semibold text-center py-2">{{ index + 1 }}/{{ cardsList.length }}</p>

                <!-- <editor-fold desc="Buttons (Show Answer | Hold)"> -->
                <div v-if="!showCurrentAnswer">
                  <div class="flex flex-wrap gap-4 justify-center align-center py-4">
                    <v-btn
                        color="primary"
                        @click="showAnswer()"
                    >
                      Show (1)
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click="hold()"
                    >
                      Hold (2)
                    </v-btn>
                  </div>
                  <p class="text-base text-gray-500 text-center py-2">You can also use the numbers 1 and 2 for
                    selections.</p>
                </div>
                <!-- </editor-fold desc="Buttons (Show Answer | Hold)"> -->

                <!-- <editor-fold desc="Buttons (Again | Hard | Good | Easy)"> -->
                <div v-if="showCurrentAnswer" class="">
                  <div class="flex flex-wrap gap-4 justify-center align-center py-4 px-2">
                    <v-btn
                        color="primary"
                        @keyup.1="answerNow('again')"
                        @click="answerNow('again')"
                    >
                      Again (1)
                    </v-btn>
                    <v-btn
                        color="primary"
                        @keyup.2="answerNow('hard')"
                        @click="answerNow('hard')"
                    >
                      Hard (2)
                    </v-btn>
                    <v-btn
                        color="primary"
                        @keyup.3="answerNow('good')"
                        @click="answerNow('good')"
                    >
                      Good (3)
                    </v-btn>
                    <v-btn
                        color="primary"
                        @keyup.4="answerNow('easy')"
                        @click="answerNow('easy')"
                    >
                      Easy (4)
                    </v-btn>
                  </div>
                  <p class="text-base text-gray-500 text-center py-2">You can also use the numbers 1,2,3, and 4 for
                    selections.</p>
                </div>
                <!-- </editor-fold desc="Buttons (Again | Hard | Good | Easy)"> -->
              </template>
              <!-- </>editor-fold desc="Good, Hard, e.t.c buttons"> -->

            </div>

          </div>
          <v-alert
              v-if="allAnswered && 'study' === purpose"
              type="success"
              border="start"
              variant="tonal"
              color="deep-purple-accent-4"
              title="Congratulations"
          >
            You have successfully completed the study session for this topic for today.
          </v-alert>
        </div>
      </form>
    </div>
  </div>
  <!--  </div>-->
</template>
<script lang="ts">

import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserDashboard from "@/composables/useUserDashboard";
import type {_AnswerLog, _Card, _CardGroup, _Study} from "@/interfaces/inter-sp";
import useImageCard from "@/composables/useImageCard";
import useUserCards from "@/composables/useUserCards";
import useMyStore from "@/composables/useMyStore";
import QuestionBasicCard from "@/vue-component/QuestionBasicCard.vue";
import QuestionImageCard from "@/vue-component/QuestionImageCard.vue";
import QuestionTableCard from "@/vue-component/QuestionTableCard.vue";
import QuestionGapCard from "@/vue-component/QuestionGapCard.vue";
import QuestionDisplay from "@/vue-component/QuestionsDisplay.vue";

export default defineComponent({
  computed: {
    currentCard(): _Card {
      return this.cardsList[this.index];
    },
    inAddQuestions() {
      return this.myStore.store.inAddCards;
    },
    oneCard(): _Card {
      return this.cardsList[this.index];
    },
    oneCardUpdated(): _Card | null {
      const theCard = this.cardsList[this.index];
      const card: _Card = {
        ...theCard,
      };

      if (!this.hasUpdated) {
        return null;
      }

      if (1 === this.tab) {
        return {
          ...card,
        };
      } else if (2 === this.tab) {
        if (card.answer_log.question && card.answer_log.answer) {
          card.question = card.answer_log.question;
          card.answer = card.answer_log.answer;
        }
        return {
          ...card,
        };
      }
      return card;
    },
    percentComplete(): number {
      return Math.round((this.index / this.cardsList.length) * 100);
    },
    hasUpdated() {
      const card: _Card = this.cardsList[this.index];
      if (!card.answer_log) {
        return false;
      }

      const answerLog: _AnswerLog = card.answer_log;

      const cardUpdatedAt = card.updated_at;
      const answerLogUpdatedAt = answerLog.updated_at;

      const dateCard = new Date(cardUpdatedAt);
      const dateAnswerLog = new Date(answerLogUpdatedAt);

      return dateCard > dateAnswerLog;
    }
  },
  methods: {
    acceptUpdatedCard() {
      //  Update card at index.
      // If old is selected, do nothing and set answer log to null.
      // If new is selected, update the card with the answer log's question and answer.
      if (1 === this.tab) {
        // Do nothing.
        this.cardsList[this.index].answer_log = null;
        console.log('Old card selected', {card: this.cardsList[this.index]});
      } else if (2 === this.tab) {
        // Update the card with the answer log's question and answer.
        this.cardsList[this.index].question = this.cardsList[this.index].answer_log.question;
        this.cardsList[this.index].answer = this.cardsList[this.index].answer_log.answer;
        this.cardsList[this.index].answer_log = null;
        console.log('New card selected', {card: this.cardsList[this.index]});
      }
    },
    _showAnswer() {
      this.showCurrentAnswer = true;
    },
    _hideAnswer() {
      this.showCurrentAnswer = false;
    },
    next() {
      // If all cards are answered, show the success message.
      const dontIncrease = this.index === (this.cardsList.length - 1);
      if (dontIncrease) {
        this.allAnswered = true;
        return;
      }

      // Hide answer.
      this._hideAnswer();

      // Update the index.
      this.index = Math.min(this.index + 1, this.cardsList.length - 1)
      // Add image card css.
      const card = this.cardsList[this.index];
      setTimeout(() => {
        this.injectImageCardCss(card);
      }, 100);
      this.recordStudyLogStart();
    },
    prev() {
      this.index = Math.max(this.index - 1, 0)
      const card = this.cardsList[this.index];
      setTimeout(() => {
        this.injectImageCardCss(card);
      }, 100);
    },
    recordStudyLogStart() {
      if ('study' === this.purpose) {
        if (this.study) {
          this.userDash.xhrRecordStudyLog(this.study, this.cardsList[this.index], 'start');
        }
      }
    },
    recordStudyLogStop() {
      if (this.study) {
        this.userDash.xhrRecordStudyLog(this.study, this.cardsList[this.index], 'stop');
      }
    },
    injectImageCardCss(card: _Card) {
      if ('image' === card.card_group.card_type) {
        useImageCard().applyPreviewCss(card.question);
        useImageCard().applyPreviewCss(card.answer);

        // useImageCard().applyPreviewCssOld(card.old_answer);

        useImageCard().applyBoxesPreviewCss(card.question.boxes);
        useImageCard().applyBoxesPreviewCss(card.answer.boxes);

        // useImageCard().applyBoxesPreviewCss(card.old_question.boxes);
        // useImageCard().applyBoxesPreviewCss(card.old_answer.boxes);
        // useImageCard().applyBoxesPreviewCssOld(card.old_answer.boxes);
      }
    },
    answerNow(grade: string) {
      // console.log(grade)
      const card = this.cardsList[this.index];
      if (this.study) {
        this.userDash
            .xhrMarkAnswer(
                this.study,
                card,
                grade,
                card.answer,
                card.question
            );
      } else {
        console.log('Study is not set', {study: this.study, card: card});
      }

      if ('again' === grade) {
        this.again();
      } else {
        this.next();
      }
    },
    handleKeyup(event: KeyboardEvent) {
      // console.log(event);
      if ('preview' === this.purpose) {
        if (event.key === 'ArrowLeft') {
          this.prev();
        }
        if (event.key === 'ArrowRight') {
          this.next();
        }
      } else if ('study' === this.purpose) {
        if (this.showCurrentAnswer) {
          if (event.key === '1') {
            this.again();
          }
          if (event.key === '2') {
            this.answer('hard');
          }
          if (event.key === '3') {
            this.answer('good');
          }
          if (event.key === '4') {
            this.answer('easy');
          }
        } else {
          if (event.key === '1') {
            this.showAnswer();
          }
          if (event.key === '2') {
            this.answer('hold')
          }
        }
      }
    },
    showAnswer() {
      this.showCurrentAnswer = true;
    },
    again() {
      // HOld, push the card to the end.
      const currentCard = this.cardsList[this.index];
      this.cardsList.push(currentCard);

      this.showCurrentAnswer = false;
      this.next();
    },
    hold() {
      // HOld, push the card to the end.
      const currentCard = this.cardsList[this.index];
      if (this.study) {
        this
            .userDash
            .xhrMarkAnswerOnHold(
                this.study,
                currentCard
            );
      }
      this.next();
    },
  },
  setup: (props, ctx) => {
    return {
      userDash: useUserDashboard(),
      myStore: useMyStore(),
    }
  },
  created() {
    this.cardsList = this.cards;
    // Shuffle the cards only when the purpose is study.
    if ('study' === this.purpose) {
      // this.cardsList = this.cardsList.sort(() => Math.random() - 0.5);
      console.log('Shuffled cards', this.cardsList);
    }

    // Set the index to start when the purpose is preview.
    if ('preview' === this.purpose) {
      this.index = this.indexToStart;
    }

    const card = this.cardsList[this.index];
    setTimeout(() => {
      this.injectImageCardCss(card);
    }, 100);
    this.recordStudyLogStart();

    this.tabsData.card = JSON.parse(JSON.stringify(this.cardsList[this.index]));
    if (this.cardsList[this.index].answer_log) {
      this.tabsData.answerLog = JSON.parse(JSON.stringify(this.cardsList[this.index].answer_log));
    }
  },
  data() {
    return {
      cardsList: [] as _Card[],
      tabShow: true,
      tab: null,
      tabsData: {
        card: null as _Card,
        answerLog: null as _AnswerLog
      },
      // questionIndex: 0,
      showCurrentAnswer: false,
      showGrade: false,
      index: 0,
      allAnswered: false,
    }
  },
  name: 'QuestionModal',
  components: {
    QuestionDisplay,
    QuestionGapCard,
    QuestionTableCard,
    QuestionImageCard,
    QuestionBasicCard,
    'ajax-action': AjaxAction,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    cards: {
      type: Array as () => _Card[],
      required: true,
    },
    showOnlyAnswers: {
      type: Boolean,
      required: false,
      default: false,
    },
    purpose: {
      type: String as () => 'preview' | 'study',
      required: false,
      default: 'preview',
    },
    userCards: {
      type: Object as () => ReturnType<typeof useUserCards>,
      required: true
    },
    selectedCards: {
      type: Array as () => _CardGroup[],
      required: false,
      default: () => [],
    },
    indexToStart: {
      type: Number,
      required: false,
      default: 0,
    },
    study: {
      type: Object as () => _Study,
      required: false,
    }
  },
  mounted() {
    document.addEventListener('keydown', this.handleKeyup);
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.handleKeyup);
  },
  watch: {
    tab(newTab: number, oldTab: number) {
      console.log('tab', {newTab, oldTab});
      this.tabShow = false;
      setTimeout(() => {
        this.tabShow = true;
      }, 100);
    }
  },
});

</script>
