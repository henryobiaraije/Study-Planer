<template>
  <!--	<editor-fold desc="Header">-->
  <h1 class="wp-heading-inline py-4">{{ pageTitle }}</h1>
  <p>Here you can assign topics to existing cards by deck groups, decks or topics.</p>
  <!--	</editor-fold  desc="Header">--><!--  Body  -->
  <br/>
  <form v-if="showMain" @submit.prevent="gapCard.createOrUpdate()"
        class="assign-topics gap-4 flex flex-col ">
    <!--  Topic to assign -->
    <div class="flex-1">
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
    <!--  Topic to assign, Group, Deck -->
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
        <!--  Topic -->
        <div v-if="assignTopic.assign.value.group" class="flex-1 mp-slide-in">
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
        <!--  Topic -->
        <div v-if="assignTopic.assign.value.deck" class="lg:w-1/3 mp-slide-in">
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
      </div>
      <!-- Card types -->
      <div class="flex flex-col lg:flex-row gap-2 mt-2">
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
      <!--  Specific cards -->
      <div class="">
          <span class="text-base block mb-2 font-medium">Specific Cards <span
              class="text-gray-400">(optional)</span></span>
        <vue-mulitiselect
            v-model="assignTopic.assign.value.topicToAssign"
            :options="allCards.searchResults.value"
            :multiple="false"
            :loading="allCards.ajaxSearch.value.sending"
            :searchable="true"
            :allowEmpty="false"
            :close-on-select="true"
            :taggable="false"
            :createTag="false"
            @search-change="allCards.search"
            placeholder="Select Cards"
            label="name"
            track-by="id"
        ></vue-mulitiselect>
      </div>
      <br/>
      <SelectedCardsAssign
          @tab-changed="activeTab = $event"
          :active-tab="activeTab"
          @card-clicked="cardSelected($event)"
          :card-items="cardItems"
          :selected-cards="selectedCards"
          :loading="allCards.ajaxSearch.value.sending"
          :found-count="allCards.tableData.value.totalRecords"
      />
      <!--      <pagination v-model="page" :records="500" :per-page="25" @paginate="myCallback"/>-->
    </div>
  </form>
  <div v-show="'found' === activeTab" class="card-pagination py-2">
    <pagination :records="allCards.tableData.value.totalRecords" v-model="page" :per-page="2" @paginate="callback"/>
  </div>

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
import type {_AssignTopic, _CardGroup} from "@/interfaces/inter-sp";
import useAssign from "@/composables/useAssign";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import useAllCards from "@/composables/useAllCards";
import SelectedCardsAssign from "@/components/SelectedCardsAssign.vue";
// @ts-ignore
import Pagination from 'v-pagination-3';

export default defineComponent({
  name: 'AdminAssignTopics',
  components: {
    SelectedCardsAssign,
    InputEditorB, InputEditor, PickImage, VueMulitiselect, TimeComp, AjaxAction, HoverNotifications,
    Pagination
  },
  props: {},
  data() {
    return {
      pageTitle: 'Assign Topics',
      showMain: false,
      selectedCards: [] as _CardGroup[],
      page: 1,
      activeTab: 'found',
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
      assignTopic: useAssign(),
      allCards: useAllCards()
    }
  },
  computed: {
    cardItems(): _CardGroup[] {
      return this.allCards.searchResults.value;
    },
  },
  created() {
    console.log('now created');
    jQuery('.all-loading').hide();
    jQuery('.all-loaded').show();
    this.showMain = true;

    this.topics.load();
    this.deckGroup.load();
    this.decks.search('');
  },
  methods: {
    spClientData,
    searchCards() {
      this.allCards.search(
          '',
          this.assignTopic.assign.value.group,
          this.assignTopic.assign.value.deck,
          this.assignTopic.assign.value.topic,
          this.assignTopic.assign.value.cardTypes
      );
    },
    cardSelected(card: _CardGroup) {
      // console.log('card selected', {event, target: event.id});
      const index = this.selectedCards.findIndex((c) => c.id === card.id);
      if (index === -1) {
        this.selectedCards.push(card);
      } else {
        this.selectedCards.splice(index, 1);
      }
    },
    callback: function (page) {
      this.page = page;
      this.allCards.page.value = page;
      this.searchCards();
      // console.log(`Page ${page} was selected. Do something about it`);
    },
  },
  // watch
  watch: {
    'assignTopic.assign.value.group': function (newVal, oldVal) {
      this.assignTopic.assign.value.deck = null;
      this.assignTopic.assign.value.topic = null;
      this.assignTopic.assign.value.specificCards = [];
      // this.assignTopic.assign.value.group = newVal;
      this.decks.search('', this.assignTopic.assign.value.deck);
      this.searchCards();
    },
    'assignTopic.assign.value.deck': function (newVal, oldVal) {
      this.assignTopic.assign.value.topic = null;
      this.assignTopic.assign.value.specificCards = [];
      // this.assignTopic.assign.value.deck = newVal;
      this.topics.search('', this.assignTopic.assign.value.deck);
      this.searchCards();
    },
    'assignTopic.assign.value.topic': function (newVal, oldVal) {
      this.assignTopic.assign.value.specificCards = [];
      // this.assignTopic.assign.value.topic = newVal;
      this.searchCards();
    },
    'assignTopic.assign.value.cardTypes': function (newVal, oldVal) {
      this.searchCards();
    },
  },
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->