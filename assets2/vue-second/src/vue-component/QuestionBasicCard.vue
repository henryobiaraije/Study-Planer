<template>
  <div
      v-html="question "
      class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2  mb-2 "></div>
  <div v-show="showCurrentAnswer" style="font-family: 'Montserrat', sans-serif;"
       v-html="answer"
       class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 !text-left "></div>
</template>
<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
  name: "QuestionBasicCard",
  props: {
    question: {
      type: String,
      required: true
    },
    answer: {
      type: String,
      required: true
    },
    showCurrentAnswer: {
      type: Boolean,
      required: true
    }
  },
  created() {
    console.log('QuestionBasicCard created');
  }
});
</script>