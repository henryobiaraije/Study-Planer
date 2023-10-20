<template>
  <!--  <div class="sp sp-modal">-->
  <form @submit.prevent="" class="modal-content min-w-[90vh]" style="height: 100%;">
    <div class="mb-4">
      <!--              <?php \StudyPlanner\load_template('shortcodes/dashboard/template-part-accept-changes'); ?>-->
    </div>
    <div v-if="null !== currentQuestion && !currentQuestion.has_updated"
         class="sp-question min-h-[65vh] flex align-items-center overflow-x-auto moxal-y-hidden"
         style="background-repeat: no-repeat;background-size: cover;"
         :style="{'background-image' : 'url('+currentQuestion?.card_group?.bg_image_url+')'}">
      <div class="flex flex-col gap-2 w-full">
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
              :src="oneCard.card_group.bg_image_url"
          >
            <v-sheet
                height="100%"
            >
              <div class="d-flex fill-height justify-center align-center">
                <!--                <div class="text-h2">-->
                <!--                  {{ slide }} Slide-->
                <!--                </div>-->

                <!-- Basic Card -->
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
                <!-- Gap Card -->
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
                <!-- Table Card -->
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

                <!-- Image Card -->
                <!--              <div v-else-if="'image' === currentQuestion.card_group.card_type" class="w-full">-->
                <!--                <div v-show="showCurrentAnswer" class="sp-image-question m-auto  mb-2">-->
                <!--                  <div class="image-area" :style="{height: currentQuestion.h+'px' }">-->
                <!--                    <div :id="'main-preview-'+currentQuestion.hash"-->
                <!--                         class="image-area-inner-preview image-card-view ">-->
                <!--									    <span v-for="(item2,itemIndex2) in currentQuestion.question.boxes"-->
                <!--                            style="font-family: 'Montserrat', sans-serif;"-->
                <!--                            :id="'sp-box-preview-'+item2.hash"-->
                <!--                            :class="{'show-box': item2.show, 'asked-box' : item2.asked, 'hide-box' : item2.hide }"-->
                <!--                            :key="item2.hash" class="sp-box-preview ">-->
                <!--										    <span v-if="item2.imageUrl.length < 2"></span>-->
                <!--                        <img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt="">-->
                <!--                      </span>-->
                <!--                    </div>-->
                <!--                  </div>-->
                <!--                </div>-->
                <!--                <div v-show="showCurrentAnswer" class="sp-image-question m-auto ">-->
                <!--                  <div class="image-area" :style="{height: currentQuestion.h+'px' }">-->
                <!--                    <div :id="'main-preview-'+currentQuestion.hash"-->
                <!--                         class="image-area-inner-preview image-card-view ">-->
                <!--									<span v-for="(item2,itemIndex2) in currentQuestion.answer.boxes"-->
                <!--                        style="font-family: 'Montserrat', sans-serif;"-->
                <!--                        :id="'sp-box-preview-'+item2.hash"-->
                <!--                        :class="{'show-box': item2.show, 'hide-box' : item2.hide, 'hide-box' : item2.hide }"-->
                <!--                        :key="item2.hash" class="sp-box-preview">-->
                <!--										<span v-if="item2.imageUrl.length < 2"></span>-->
                <!--										<img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt="">-->
                <!--									</span>-->
                <!--                    </div>-->
                <!--                  </div>-->
                <!--                </div>-->
                <!--              </div>-->
                <!--              <div v-if="userDash.ajaxLoadingCard.sending" style="text-align: center;flex: 12;font-size: 50px;"><i-->
                <!--                  class="fa fa-spin fa-spinner"></i></div>-->
                <!--              </div>-->
              </div>
            </v-sheet>
          </v-carousel-item>
        </v-carousel>
        <div class="d-flex justify-space-around align-center py-4">
          <v-btn
              color="primary"
              @click="index = Math.max(index - 1, 0)"
          >
            Prev
          </v-btn>
          <span class="text-xl font-semibold">{{ index + 1 }}/{{ cards.length }}</span>
          <v-btn
              color="primary"
              @click="index = Math.min(index + 1, cards.length - 1)"
          >
            Next
          </v-btn>
        </div>
      </div>
    </div>
  </form>
  <!--  </div>-->
</template>
<script lang="ts">

import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserDashboard from "@/composables/useUserDashboard";
import type {_Card} from "@/interfaces/inter-sp";

export default defineComponent({
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
  },
  data() {
    return {
      questionIndex: 0,
      showCurrentAnswer: false,
      showGrade: false,
      index: 0
    }
  },
  setup: (props, ctx) => {
    return {
      // userDash: useUserDashboard(),
    }
  },
  computed: {
    currentQuestion(): _Card {
      return this.cards[this.questionIndex];
    },
  },
  created() {

  },
  methods: {
    _showAnswer() {
      this.showCurrentAnswer = true;
    },
  }
});

</script>
