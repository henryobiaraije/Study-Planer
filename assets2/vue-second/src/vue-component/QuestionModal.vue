<template>
  <!--  <div class="sp sp-modal">-->
  <v-progress-linear :model-value="percentComplete" :height="5" color="primary"></v-progress-linear>
  <div class="chose-updated-or-old-card flex flex-col gap-3">
    <div class="info text-center py-3">This card has been updated since the last time you answered it.</div>
    <v-card>
      <v-tabs
          v-model="tab"
          align-tabs="center"
          color="deep-purple-accent-4"
      >
        <v-tab :value="1">Old Card</v-tab>
        <v-tab :value="2">New Card</v-tab>
      </v-tabs>
    </v-card>
    <div class="py-4 text-center">
      <button class="py-2 px-8 text-base font-bold rounded-md bg-gray-100 border border-solid border-gray-300 hover:bg-gray-300 ">Use
        this version
      </button>
    </div>
  </div>
  <div class="admin-image-card">
    <form @submit.prevent="" class="modal-content min-w-[85vw]" style="height: 100%;">
      <div class="mb-4">
        <!--              <?php \StudyPlannerPro\load_template('shortcodes/dashboard/template-part-accept-changes'); ?>-->
      </div>
      <div v-if="(null !== currentQuestion) && (undefined !== currentQuestion) && (!currentQuestion.has_updated)"
           class="sp-question min-h-[65vh] flex align-items-center overflow-x-auto moxal-y-hidden"
           style="background-repeat: no-repeat;background-size: cover;"
           :style="{'background-image' : 'url('+currentQuestion?.card_group?.bg_image_url+')'}">
        <div v-if="!allAnswered" class="flex flex-col gap-2 w-full">
          <div class="d-flex fill-height justify-center align-center max-h-[55vh] overflow-auto">
            <!-- <editor-fold desc="Basic Card"> -->
            <div v-if="'basic' === currentQuestion?.card_group.card_type"
                 class="sp-basic-question w-full text-center"
                 style="font-family: 'Montserrat', sans-serif;">
              <QuestionBasicCard
                  :show-current-answer="showCurrentAnswer"
                  :answer="currentQuestion.answer"
                  question="currentQuestion.question"
              />
            </div>
            <!-- </editor-fold desc="Basic Card"> -->

            <!-- <editor-fold desc="Gap Card"> -->
            <div
                v-else-if="'gap' === currentQuestion.card_group.card_type"
                class="mp-ql-editor-content-wrapper">

              <QuestionGapCard
                  :current-question="currentQuestion"
                  :show-current-answer="showCurrentAnswer"
                  :show-only-answers="showOnlyAnswers"
                  :_show-answer="_showAnswer"
              />

            </div>
            <!-- </editor-fold desc="Gap Card"> -->

            <!-- <editor-fold desc="Table Card"> -->
            <div v-else-if="'table' === currentQuestion.card_group.card_type"
                 class="sp-table-question m-auto w-full">

              <QuestionTableCard
                  :question="currentQuestion.question"
                  :answer="currentQuestion.answer"
                  :show-current-answer="showCurrentAnswer"
                  :show-only-answers="showOnlyAnswers"
                  :one-card="oneCard"
              ></QuestionTableCard>

            </div>
            <!-- </editor-fold desc="Table Card"> -->

            <!-- <editor-fold desc="Image Card"> -->
            <div v-else-if="'image' === oneCard.card_group.card_type" class="w-full">
              <QuestionImageCard
                  :one-card="oneCard"
                  :show-current-answer="showCurrentAnswer"
                  :show-only-answers="showOnlyAnswers"
              />
            </div>
            <!-- </editor-fold desc="Image Card"> -->
          </div>
          <!--              </v-sheet>-->
          <!--            </v-carousel-item>-->
          <!--          </v-carousel>-->

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
                <span class="text-xl font-semibold">{{ index + 1 }}/{{ cards.length }}</span>
                <span v-if="inAddQuestions" class="flex-initial">
                  <v-switch
                      v-model="userCards.form.value.selectedCards"
                      color="primary"
                      hide-details
                      :value="cards[index].card_group"
                      label="Selected"
                  ></v-switch>
                </span>
              </div>
              <span class="flex-1 text-center">
                <v-btn
                    :disabled="index === (cards.length - 1)"
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
              <span class="text-xl font-semibold">{{ index + 1 }}/{{ cards.length }}</span>
              <span v-if="inAddQuestions" class="flex-initial">
                  <v-switch
                      v-model="userCards.form.value.selectedCards"
                      color="primary"
                      hide-details
                      :value="cards[index].card_group"
                      label="Selected"
                  ></v-switch>
                </span>
            </div>
            <p class="flex-1 w-full py-4 text-center text-base text-gray-500">You can use left and right arrow keys to
              move through the cards</p>
          </div>
          <!-- </editor-fold desc="Prev & Next"> -->

          <template v-if="'study' === purpose">
            <p class="text-xl font-semibold text-center py-2">{{ index + 1 }}/{{ cards.length }}</p>

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
                    @keyup.1="answer('again')"
                    @click="answer('again')"
                >
                  Again (1)
                </v-btn>
                <v-btn
                    color="primary"
                    @keyup.2="answer('hard')"
                    @click="answer('hard')"
                >
                  Hard (2)
                </v-btn>
                <v-btn
                    color="primary"
                    @keyup.3="answer('good')"
                    @click="answer('good')"
                >
                  Good (3)
                </v-btn>
                <v-btn
                    color="primary"
                    @keyup.4="answer('easy')"
                    @click="answer('easy')"
                >
                  Easy (4)
                </v-btn>
              </div>
              <p class="text-base text-gray-500 text-center py-2">You can also use the numbers 1,2,3, and 4 for
                selections.</p>
            </div>
            <!-- </editor-fold desc="Buttons (Again | Hard | Good | Easy)"> -->
          </template>

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
  <!--  </div>-->
</template>
<script lang="ts">

import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserDashboard from "@/composables/useUserDashboard";
import type {_Card, _CardGroup, _Study} from "@/interfaces/inter-sp";
import useImageCard from "@/composables/useImageCard";
import {spClientData} from "@/functions";
import useUserCards from "@/composables/useUserCards";
import useMyStore from "@/composables/useMyStore";
import QuestionBasicCard from "@/vue-component/QuestionBasicCard.vue";
import QuestionImageCard from "@/vue-component/QuestionImageCard.vue";
import QuestionTableCard from "@/vue-component/QuestionTableCard.vue";
import QuestionGapCard from "@/vue-component/QuestionGapCard.vue";

export default defineComponent({
  computed: {
    currentQuestion(): _Card {
      return this.cards[this.index];
    },
    inAddQuestions() {
      return this.myStore.store.inAddCards;
    },
    oneCard(): _Card {
      return this.cards[this.index];
    },
    percentComplete(): number {
      return Math.round((this.index / this.cards.length) * 100);
    },
  },
  methods: {
    _showAnswer() {
      this.showCurrentAnswer = true;
    },
    _hideAnswer() {
      this.showCurrentAnswer = false;
    },
    next() {
      // If all cards are answered, show the success message.
      const dontIncrease = this.index === (this.cards.length - 1);
      if (dontIncrease) {
        this.allAnswered = true;
        return;
      }

      // Hide answer.
      this._hideAnswer();

      // Update the index.
      this.index = Math.min(this.index + 1, this.cards.length - 1)
      // Add image card css.
      const card = this.cards[this.index];
      setTimeout(() => {
        this.injectImageCardCss(card);
      }, 100);
      this.recordStudyLogStart();
    },
    prev() {
      this.index = Math.max(this.index - 1, 0)
      const card = this.cards[this.index];
      setTimeout(() => {
        this.injectImageCardCss(card);
      }, 100);
    },
    recordStudyLogStart() {
      if ('study' === this.purpose) {
        if (this.study) {
          this.userDash.xhrRecordStudyLog(this.study, this.cards[this.index], 'start');
        }
      }
    },
    recordStudyLogStop() {
      if (this.study) {
        this.userDash.xhrRecordStudyLog(this.study, this.cards[this.index], 'stop');
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
    answer(grade: string) {
      // console.log(grade)
      const card = this.cards[this.index];
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
      const currentCard = this.cards[this.index];
      this.cards.push(currentCard);

      this.showCurrentAnswer = false;
      this.next();
    },
    hold() {
      // HOld, push the card to the end.
      const currentCard = this.cards[this.index];
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
    // Shuffle the cards only when the purpose is study.
    if ('study' === this.purpose) {
      // this.cards = this.cards.sort(() => Math.random() - 0.5);
      console.log('Shuffled cards', this.cards);
    }

    // Set the index to start when the purpose is preview.
    if ('preview' === this.purpose) {
      this.index = this.indexToStart;
    }

    const card = this.cards[this.index];
    setTimeout(() => {
      this.injectImageCardCss(card);
    }, 100);
    this.recordStudyLogStart();
  },
  data() {
    return {
      tab: null,
      // questionIndex: 0,
      showCurrentAnswer: false,
      showGrade: false,
      index: 0,
      allAnswered: false,
    }
  },
  name: 'QuestionModal',
  components: {
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
  }
});

</script>
