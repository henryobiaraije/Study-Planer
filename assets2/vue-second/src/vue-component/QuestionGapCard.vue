<template>
  <div class="ql-editor">
    <div
        class="sp-gap-question w-full !text-left ">
      <div v-show="!showCurrentAnswer && !showOnlyAnswers"
           @click="_showAnswer()" v-html="oneCard.question"
           style="font-family: 'Montserrat', sans-serif;"
           class=" p-2 rounded-2 text-center mb-4 lg:max-w-4xl m-auto "></div>
      <!-- <hr style="border-top-width: 1px;border-color: #b2b2b2;margin: 10px;"/> -->
      <div v-show="showCurrentAnswer || showOnlyAnswers" v-html="oneCard.answer"
           style="font-family: 'Montserrat', sans-serif;"
           class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 !text-left "></div>
    </div>
  </div>
</template>
<script lang="ts">
import {defineComponent} from "vue";
import type {_Card, _CardGroup, _Study} from "@/interfaces/inter-sp";

export default defineComponent({
  name: "QuestionGapCard",
  props: {
    showCurrentAnswer: {
      type: Boolean,
      required: true
    },
    showOnlyAnswers: {
      type: Boolean,
      required: true
    },
    _showAnswer: {
      type: Function,
      required: true
    },
    oneCard: {
      type: Object as () => _Card,
      required: true
    }
  },
  created() {
    console.log('QuestionGapCard created');
  }
});
</script>