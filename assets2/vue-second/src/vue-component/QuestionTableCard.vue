<template>
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
</template>
<script lang="ts">
import {defineComponent} from "vue";
import type {_Card, _CardGroup, _Study} from "@/interfaces/inter-sp";

export default defineComponent({
  name: "QuestionTableCard",
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
    },
    showOnlyAnswers: {
      type: Boolean,
      required: true
    },
    oneCard: {
      type: Object as () => _Card,
      required: true
    }
  },
  created() {
    console.log('QuestionTableCard created');
  }
});
</script>