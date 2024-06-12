<template>
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
</template>
<script lang="ts">
import {defineComponent} from "vue";
import type {_Card, _CardGroup, _Study} from "@/interfaces/inter-sp";

export default defineComponent({
  name: "QuestionImageCard",
  props: {
    oneCard: {
      type: Object as () => _Card,
      required: true
    },
    showCurrentAnswer: {
      type: Boolean,
      required: true
    },
    showOnlyAnswers: {
      type: Boolean,
      required: true
    }
  },
  created() {
    console.log('QuestionImageCard created');
  }
});
</script>