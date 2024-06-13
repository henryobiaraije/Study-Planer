<template>
  <div class="d-flex fill-height justify-center align-center max-h-[55vh] overflow-auto">
    <!-- <editor-fold desc="Basic Card"> -->
    <div v-if="'basic' === currentCard?.card_group.card_type"
         class="sp-basic-question w-full text-center"
         style="font-family: 'Montserrat', sans-serif;">
      <QuestionBasicCard
          :show-current-answer="showCurrentAnswer"
          :answer="answer"
          :question="question"
      />
    </div>
    <!-- </editor-fold desc="Basic Card"> -->

    <!-- <editor-fold desc="Gap Card"> -->
    <div
        v-else-if="'gap' === currentCard.card_group.card_type"
        class="mp-ql-editor-content-wrapper">
      <QuestionGapCard
          :question="question"
          :answer="answer"
          :show-current-answer="showCurrentAnswer"
          :show-only-answers="showOnlyAnswers"
          :_show-answer="_showAnswer"
      />

    </div>
    <!-- </editor-fold desc="Gap Card"> -->

    <!-- <editor-fold desc="Table Card"> -->
    <div v-else-if="'table' === currentCard.card_group.card_type"
         class="sp-table-question m-auto w-full">

      <QuestionTableCard
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
</template>

<script lang="ts">

import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserDashboard from "@/composables/useUserDashboard";
import type {_AnswerLog, _Card, _CardGroup, _Study} from "@/interfaces/inter-sp";
import useImageCard from "@/composables/useImageCard";
import {spClientData} from "@/functions";
import useUserCards from "@/composables/useUserCards";
import useMyStore from "@/composables/useMyStore";
import QuestionBasicCard from "@/vue-component/QuestionBasicCard.vue";
import QuestionImageCard from "@/vue-component/QuestionImageCard.vue";
import QuestionTableCard from "@/vue-component/QuestionTableCard.vue";
import QuestionGapCard from "@/vue-component/QuestionGapCard.vue";

export default defineComponent({
  computed: {},
  methods: {},
  setup: (props, ctx) => {
    return {
      // userDash: useUserDashboard(),
      // myStore: useMyStore(),
    }
  },
  created() {

  },
  data() {
    return {}
  },
  name: 'QuestionDisplay',
  components: {
    QuestionGapCard,
    QuestionTableCard,
    QuestionImageCard,
    QuestionBasicCard,
    'ajax-action': AjaxAction,
  },
});

</script>
