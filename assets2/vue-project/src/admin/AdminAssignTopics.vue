<template>
  <!--	<editor-fold desc="Header">-->
  <h1 class="wp-heading-inline py-4">{{ pageTitle }}</h1>
  <p>Here you can assign topics to existing cards by deck groups, decks or topics.</p>
  <!--	</editor-fold  desc="Header">--><!--  Body  -->
  <br/>
  <form v-if="showMain" @submit.prevent="gapCard.createOrUpdate()"
        class="assign-topics gap-4 flex flex-col ">
    <div class="">
      <span class="text-base block mb-2 font-medium">Topic to assign to* </span>
      <vue-mulitiselect
          v-model="assignTopic.assign.value.topicToAssign"
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
    <div class="flex flex-col gap-2">
      <p>Filter cards below and add them to the above topic.</p>
      <div class="flex flex-col lg:flex-row gap-2">
        <div class="flex-1">
          <span class="text-base block mb-2 font-medium">Group*</span>
          <vue-mulitiselect
              v-model="assignTopic.assign.value.group"
              :options="deckGroup.searchResults.value"
              :multiple="false"
              :loading="deckGroup.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="deckGroup.search"
              placeholder="Deck Groups"
              label="name"
              track-by="id"
          ></vue-mulitiselect>
        </div>
        <div class="flex-1">
          <span class="text-base block mb-2 font-medium">Subject <span class="text-gray-400">(optional)</span></span>
          <vue-mulitiselect
              v-model="assignTopic.assign.value.deck"
              :options="decks.searchResults.value"
              :multiple="false"
              :loading="decks.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="decks.search"
              placeholder="Decks"
              label="name"
              track-by="id"
              class="border border-solid border-sp-200"
          ></vue-mulitiselect>
        </div>
      </div>
      <div class="flex flex-col lg:flex-row gap-2 mt-2">
        <div class="lg:w-1/3">
          <span class="text-base block mb-2 font-medium">Topic <span class="text-gray-400">(optional)</span></span>
          <vue-mulitiselect
              v-model="assignTopic.assign.value.topic"
              :options="topics.searchResults.value"
              :multiple="false"
              :loading="topics.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="topics.search"
              placeholder="Select Topic"
              label="name"
              track-by="id"
          ></vue-mulitiselect>
        </div>
        <div class="lg:w-2/3 flex flex-col">
          <span class="flex-initial text-base block mb-2 font-medium">Card Types<span
              class="text-gray-400"> (optional)</span></span>
          <div
              class="card-types flex-1 list-none p-2 m-0 flex gap-4 items-center border border-solid !border-sp-500 bg-white rounded">
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500">
              <span class="block items-center">
                <input type="checkbox" value="basic" v-model="assignTopic.assign.value.cardTypes">
              </span>
              <span class="'block no-break">Basic Cards</span>
            </label>
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500">
              <span class="block ">
                <input type="checkbox" value="gap" v-model="assignTopic.assign.value.cardTypes">
              </span>
              <span class="block no-break">Gap Cards</span>
            </label>
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500">
              <span class="block ">
                <input type="checkbox" value="table" v-model="assignTopic.assign.value.cardTypes">
              </span>
              <span class="block">Table Cards</span>
            </label>
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500">
              <span class="block ">
                <input type="checkbox" value="image" v-model="assignTopic.assign.value.cardTypes">
              </span>
              <span class="block no-break">Image Cards</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </form>

  <hover-notifications></hover-notifications>
</template>
<script lang="ts">
import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import HoverNotifications from "@/vue-component/HoverNotifications.vue";
import TimeComp from "@/vue-component/TimeComp.vue";
import useTagSearch from "@/composables/useTagSearch";
import useDecks from "@/composables/useDecks";
import useBasicCard from "@/composables/useBasicCard";
import PickImage from "@/vue-component/PickImage.vue";
import useGapCard from "@/composables/useGapCard";
import VueMulitiselect from "vue-multiselect";
import {spClientData} from "@/functions";
import InputEditor from "@/vue-component/InputEditor.vue";
import InputEditorB from "@/vue-component/InputEditorB.vue";
import useTopics from "@/composables/useTopics";
import useCollections from "@/composables/useCollections";
import type {_AssignTopic} from "@/interfaces/inter-sp";
import useAssign from "@/composables/useAssign";
import useDeckGroupLists from "@/composables/useDeckGroupLists";

export default defineComponent({
  name: 'AdminAssignTopics',
  components: {InputEditorB, InputEditor, PickImage, VueMulitiselect, TimeComp, AjaxAction, HoverNotifications},
  props: {},
  data() {
    return {
      pageTitle: 'Assign Topics',
      showMain: false,
    }
  },
  setup: (props, ctx) => {
    return {
      decks: useDecks(status),
      deckGroup: useDeckGroupLists(),
      searchTags: useTagSearch(),
      basicCard: useBasicCard(),
      gapCard: useGapCard(),
      topics: useTopics(),
      collections: useCollections(),
      assignTopic: useAssign()
    }
  },
  computed: {},
  created() {
    console.log('now created');
    this.topics.load()
    //     .forEach(item => {
    //   item.then(() => {
    jQuery('.all-loading').hide();
    jQuery('.all-loaded').show();
    this.showMain = true;
    //   }).catch(() => {
    //     // jQuery('.all-loading').hide();
    //     // jQuery('.all-error').show();
    //   });
    // })
    // this.gapCard.load().then(() => {
    //   jQuery('.all-loading').hide();
    //   jQuery('.all-loaded').show();
    //   this.showMain = true;
    // }).catch(() => {
    //   jQuery('.all-loading').hide();
    //   jQuery('.all-error').show();
    // });

  },
  methods: {spClientData}
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->