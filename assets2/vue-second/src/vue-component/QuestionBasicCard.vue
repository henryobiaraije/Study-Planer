<template>
  <div
      v-html="oneCard.question"
      v-show="!showCurrentAnswer && !showOnlyAnswers"
      class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2  mb-2 "></div>
  <div
      v-show="showCurrentAnswer || showOnlyAnswers"
      style="font-family: 'Montserrat', sans-serif;"
       v-html="oneCard.answer"
       class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 !text-left "></div>
</template>
<script lang="ts">
import {defineComponent} from "vue";
import {_Card} from "@/interfaces/inter-sp";

export default defineComponent({
  name: "QuestionBasicCard",
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
    // console.log('QuestionBasicCard created');
  }
});
</script>