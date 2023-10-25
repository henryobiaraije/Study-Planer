<template>
  <!--  <div class="sp sp-modal">-->
  <div class="admin-image-card">
    <form @submit.prevent="" class="modal-content min-w-[90vw]" style="height: 100%;">
      <div class="mb-4">
        <!--              <?php \StudyPlannerPro\load_template('shortcodes/dashboard/template-part-accept-changes'); ?>-->
      </div>
      <div v-if="null !== currentQuestion && !currentQuestion.has_updated"
           class="sp-question min-h-[65vh] flex align-items-center overflow-x-auto moxal-y-hidden"
           style="background-repeat: no-repeat;background-size: cover;"
           :style="{'background-image' : 'url('+currentQuestion?.card_group?.bg_image_url+')'}">
        <div v-if="!allAnswered" class="flex flex-col gap-2 w-full">
          <v-carousel
              height="400"
              hide-delimiters
              progress="primary"
              v-model="index"
              :show-arrows="false"
          >
            <v-carousel-item
                v-for="(oneCard, cardIndex) in cards"
                :key="cardIndex"
            >
              <v-sheet
                  height="100%"
                  style="background: transparent !important;"
              >
                <div class="d-flex fill-height justify-center align-center">
                  <!--                <div class="text-h2">-->
                  <!--                  {{ slide }} Slide-->
                  <!--                </div>-->

                  <!-- <editor-fold desc="Basic Card"> -->
                  <div v-if="'basic' === currentQuestion?.card_group.card_type"
                       class="sp-basic-question w-full text-center"
                       style="font-family: 'Montserrat', sans-serif;">
                    <div
                        v-html="(currentQuestion.card_group.reverse || showOnlyAnswers) ? currentQuestion.answer : currentQuestion.question "
                        class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center mb-2"></div>
                    <!-- <hr style="border-top-width: 1px;border-color: #b2b2b2;margin: 10px;"/> -->
                    <div v-show="showCurrentAnswer" style="font-family: 'Montserrat', sans-serif;"
                         v-html="(currentQuestion.card_group.reverse) ? currentQuestion.question : currentQuestion.answer"
                         class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
                  </div>
                  <!-- </editor-fold desc="Basic Card"> -->

                  <!-- <editor-fold desc="Gap Card"> -->
                  <div v-else-if="'gap' === currentQuestion.card_group.card_type"
                       class="sp-gap-question w-full text-center ">
                    <div v-show="!showCurrentAnswer && !showOnlyAnswers"
                         @click="_showAnswer()" v-html="currentQuestion.question"
                         style="font-family: 'Montserrat', sans-serif;"
                         class=" p-2 rounded-2 text-center mb-4 lg:max-w-4xl m-auto "></div>
                    <!-- <hr style="border-top-width: 1px;border-color: #b2b2b2;margin: 10px;"/> -->
                    <div v-show="showCurrentAnswer || showOnlyAnswers" v-html="currentQuestion.answer"
                         style="font-family: 'Montserrat', sans-serif;"
                         class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
                  </div>
                  <!-- </editor-fold desc="Gap Card"> -->

                  <!-- <editor-fold desc="Table Card"> -->
                  <div v-else-if="'table' === currentQuestion.card_group.card_type"
                       class="sp-table-question m-auto w-full">
                    <!--                  <table v-show="!showCurrentAnswer"-->
                    <!--                         @click="_showAnswer()" v-if="currentQuestion.question.length > 0"-->
                    <!--                         class="table gap-table w-full p-2 bg-sp-100 rounded  mb-2"-->
                    <!--                         style="font-family: 'Montserrat', sans-serif;">-->
                    <!-- Table Question -->
                    <v-table v-if="!showCurrentAnswer && !showOnlyAnswers">
                      <thead>
                      <tr>
                        <th v-for="(column,columnIndex) in oneCard.question[0]"
                            class="table-cell border-1 border-sp-200 text-center">
                          <div v-html="column"></div>
                        </th>
                      </tr>
                      </thead>
                      <tbody>
                      <template v-for="(row,rowIndex) in oneCard.question">
                        <tr v-if="rowIndex !== 0"
                            :class="{'bg-gray-100' : (rowIndex / 2 > 0)}"
                        >
                          <td v-for="(column,columnIndex) in row" class="table-cell border-1 border-sp-200 text-center">
                            <div v-html="column"></div>
                          </td>
                        </tr>
                      </template>
                      </tbody>
                    </v-table>
                    <v-table v-if="showCurrentAnswer || showOnlyAnswers">
                      <thead>
                      <tr>
                        <th v-for="(column,columnIndex) in oneCard.answer[0]"
                            class="table-cell border-1 border-sp-200 text-center">
                          <div v-html="column"></div>
                        </th>
                      </tr>
                      </thead>
                      <tbody>
                      <template v-for="(row,rowIndex) in oneCard.answer">
                        <tr v-if="rowIndex !== 0"
                            :class="{'bg-gray-100' : (rowIndex / 2 > 0)}"
                        >
                          <td v-for="(column,columnIndex) in row" class="table-cell border-1 border-sp-200 text-center">
                            <div v-html="column"></div>
                          </td>
                        </tr>
                      </template>
                      </tbody>
                    </v-table>
                  </div>
                  <!-- </editor-fold desc="Table Card"> -->

                  <!-- <editor-fold desc="Image Card"> -->
                  <div v-else-if="'image' === oneCard.card_group.card_type" class="w-full">
                    <div v-show="!showOnlyAnswers && !showCurrentAnswer" class="sp-image-question m-auto mb-2 relative">
                      <div class="image-area" :style="{height: oneCard.question.h+'px' }">
                        <!--                    <div class="image-area" :style="{height: 300+'px' }">-->
                        <div :id="'main-preview-'+oneCard.question.hash"
                             class="image-area-inner-preview image-card-view inline-block relative">
                           <span v-for="(box,boxIndex) in oneCard.question.boxes"
                                 style="font-family: 'Montserrat', sans-serif;"
                                 :id="'sp-box-preview-'+box.hash"
                                 :class="{'show-box': box.show, 'asked-box' : box.asked, 'hide-box' : box.hide }"
                                 :key="box.hash"
                                 class="sp-box-preview relative inline-block">
                             <span v-if="box.imageUrl.length < 2"></span>
                            <img v-if="box.imageUrl.length > 0" :src="box.imageUrl" alt="">
                          </span>
                        </div>
                      </div>
                    </div>
                    <div v-show="showCurrentAnswer || showOnlyAnswers" class="sp-image-question m-auto ">
                      <div class="image-area" :style="{height: oneCard.answer.h+'px' }">
                        <div :id="'main-preview-'+oneCard.answer.hash"
                             class="image-area-inner-preview image-card-view ">
                               <span v-for="(item2,itemIndex2) in oneCard.answer.boxes"
                                     style="font-family: 'Montserrat', sans-serif;"
                                     :id="'sp-box-preview-'+item2.hash"
                                     :class="{'show-box': item2.show, 'hide-box' : item2.hide }"
                                     :key="item2.hash" class="sp-box-preview">
                                  <span v-if="item2.imageUrl.length < 2"></span>
                                  <img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl"
                                       alt="">
                               </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- </editor-fold desc="Image Card"> -->
                </div>
              </v-sheet>
            </v-carousel-item>
          </v-carousel>

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
              <div class="flex flex-1 gap-4 justify-center items-center">
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
              <div class="flex flex-wrap gap-4 justify-center align-center py-4">
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
import type {_Card, _CardGroup} from "@/interfaces/inter-sp";
import useImageCard from "@/composables/useImageCard";
import {spClientData} from "@/functions";
import useUserCards from "@/composables/useUserCards";
import useMyStore from "@/composables/useMyStore";

export default defineComponent({
  computed: {
    currentQuestion(): _Card {
      return this.cards[this.index];
    },
    inAddQuestions() {
      return this.myStore.store.inAddCards;
    }
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
        this.userDash.xhrRecordStudyLog(spClientData().user_study, this.cards[this.index], 'start');
      }
    },
    recordStudyLogStop() {
      this.userDash.xhrRecordStudyLog(spClientData().user_study, this.cards[this.index], 'stop');
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
      this.userDash
          .xhrMarkAnswer(
              spClientData().user_study,
              card,
              grade,
              card.answer,
              card.question
          );

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
      this
          .userDash
          .xhrMarkAnswerOnHold(
              spClientData().user_study,
              currentCard
          );
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
      this.cards = this.cards.sort(() => Math.random() - 0.5);
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
      // questionIndex: 0,
      showCurrentAnswer: false,
      showGrade: false,
      index: 0,
      allAnswered: false,
    }
  },
  name: 'QuestionModal',
  components: {
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
  },
  mounted() {
    document.addEventListener('keydown', this.handleKeyup);
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.handleKeyup);
  }
});

</script>
