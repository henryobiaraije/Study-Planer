<template>
  <div class="flex flex-col gap-4">
    <!--	<editor-fold desc="Header">-->
    <h1 class="wp-heading-inline py-4">{{ pageTitle }}</h1>
    <p>Here you can assign topics to existing cards by deck groups, decks or topics.</p>

    <!--  Topic to assign -->
    <div class="flex-1">
      <span class="text-base block mb-2 font-medium">Topic to assign to* </span>
      <vue-mulitiselect
          v-model="userCards.form.value.topicToAssign"
          :options="topics.searchResults.value"
          :multiple="false"
          :loading="topics.ajaxSearch.value.sending"
          :searchable="true"
          :allowEmpty="false"
          :close-on-select="true"
          :taggable="false"
          :createTag="false"
          @search-change="topics.search"
          placeholder="Topic"
          label="name"
          track-by="id"
      ></vue-mulitiselect>
    </div>

    <p>Filter cards below and add them to the above topic.</p>

    <CardSelector :user-cards="userCards"/>

    <!--  What to do -->
    <div v-if="form.topicToAssign"
         class="action-buttons-here mp-slide-in flex flex-wrap gap-2">
      <div class="flex flex-col gap-4">
        <div class="what-to-do flex flex-wrap gap-2 items-center">
          <label v-for="(item,itemIndex) in whatToDoList"
                 class="flex gap-2 p-2 justify-start items-center hover:text-sp-500"
                 :class="[item.value ===  userCards.form.value.whatToDo ? 'text-sp-500' : '']"
          >
            <span class="flex items-center"><input type="radio" v-model="userCards.form.value.whatToDo"
                                                   :value="item.value"/></span>
            <span class="no-break">{{ item.title }}</span>
          </label>
        </div>
        <ajax-action
            button-text="Assign"
            css-classes="button"
            icon="fa fa-save"
            @click="userCards.assignTopics"
            :ajax="userCards.ajaxAssignTopics.value">
        </ajax-action>
      </div>
    </div>
  </div>

  <hover-notifications></hover-notifications>
</template>
<script lang="ts">


import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import HoverNotifications from "@/vue-component/HoverNotifications.vue";
import VueMulitiselect from "vue-multiselect";
import useUserCards from "@/composables/useUserCards";
import useTopics from "@/composables/useTopics";
import AjaxAction from "@/vue-component/AjaxAction.vue";

export default defineComponent({
  name: 'AdminAssignTopics',
  components: {AjaxAction, VueMulitiselect, HoverNotifications, CardSelector},
  props: {},
  data() {
    return {
      pageTitle: 'Assign Topics',
      showMain: false,
      whatToDoList: [
        {
          title: 'Assign selected cards',
          value: 'selected_cards'
        },
        {
          title: 'Assign to cards in selected deck group',
          value: 'selected_group'
        },
        {
          title: 'Assign to cards in selected deck',
          value: 'selected_deck'
        },
        {
          title: 'Assign to cards in selected topic',
          value: 'selected_topic'
        }
      ],
    }
  },
  setup: (props, ctx) => {
    return {
      userCards: useUserCards(),
      topics: useTopics(),
    }
  },
  computed: {
    form() {
      return this.userCards.form.value;
    }
  },
  created() {
    this.topics.search('');
  },
  methods: {},
  // watch
  watch: {},
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->