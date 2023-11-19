<template>
  <div class="assign-topics gap-4 flex flex-col ">
    <!--   Group, Deck, Topic -->
    <div class="flex flex-col gap-3">
      <div class="flex flex-col lg:flex-row gap-4 ">
        <!--  Group -->
        <div class="flex-1 group ">
          <span class="text-base items-center flex gap-2 w-full mb-2 font-medium">
            <span>Group*</span>
            <span @click="clearGroup"
                  class="hidden px-2 py-1 text-xs bg-gray-300 font-normal group-hover:inline-block cursor-pointer hover:bg-gray-400">Clear</span>
          </span>
          <vue-mulitiselect
              v-model="userCards.form.value.group"
              :options="deckGroupMulti.searchResults.value"
              :multiple="false"
              :loading="deckGroupMulti.ajaxSearch.value.sending || deckGroupMulti.ajax.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="deckGroupMulti.search"
              placeholder="Deck Groups"
              label="name"
              track-by="id"
          ></vue-mulitiselect>
        </div>
        <!--  Deck -->
        <div v-if="userCards.form.value.group" class="group flex-1 mp-slide-in">
          <span class="text-base flex items-center gap-2 w-full mb-2 font-medium">
            <span>Subject <span class="text-gray-400">(optional)</span></span>
            <span @click="clearDeck"
                  class="hidden px-2 py-1 text-xs bg-gray-300 font-normal group-hover:inline-block cursor-pointer hover:bg-gray-400">Clear</span>
          </span>
          <vue-mulitiselect
              v-model="userCards.form.value.deck"
              :options="decksMulti.searchResults.value"
              :multiple="false"
              :loading="decksMulti.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="(query) => decksMulti.search(query,form.group as any)"
              placeholder="Decks"
              label="name"
              track-by="id"
              class="border border-solid border-sp-200"
          ></vue-mulitiselect>
        </div>
        <!--  Topic -->
        <div v-if="userCards.form.value.deck" class="group lg:w-1/3 mp-slide-in">
          <span class="text-base flex items-center gap-2 w-full mb-2 font-medium">
            <span>Topic <span class="text-gray-400">(optional)</span></span>
            <span @click="clearTopic"
                  class="hidden px-2 py-1 text-xs bg-gray-300 font-normal group-hover:inline-block cursor-pointer hover:bg-gray-400">Clear</span>
          </span>
          <vue-mulitiselect
              v-model="userCards.form.value.topic"
              :options="topicsMulti.searchResults.value"
              :multiple="false"
              :loading="topicsMulti.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="(query) => topicsMulti.search(query,form.deck as any)"
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
              class="card-types flex-1 list-none p-2 m-0 flex flex-wrap gap-4 items-center border border-solid !border-sp-500 bg-white rounded">
            <label class="flex flex-auto gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('basic') ? 'text-sp-500' : '']"
            >
              <span class="block items-center">
                <input type="checkbox" value="basic" v-model="userCards.form.value.cardTypes">
              </span>
              <span class="'block no-break">Basic Cards</span>
            </label>
            <label class="flex flex-auto gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('gap') ? 'text-sp-500' : '']"
            >
              <span class="block ">
                <input type="checkbox" value="gap" v-model="userCards.form.value.cardTypes">
              </span>
              <span class="block no-break">Gap Cards</span>
            </label>
            <label class="flex flex-auto gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('table') ? 'text-sp-500' : '']"
            >
              <span class="block ">
                <input type="checkbox" value="table" v-model="userCards.form.value.cardTypes">
              </span>
              <span class="block">Table Cards</span>
            </label>
            <label class="flex flex-auto gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('image') ? 'text-sp-500' : '']"
            >
              <span class="block ">
                <input type="checkbox" value="image" v-model="userCards.form.value.cardTypes">
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
            v-model="userCards.oneSpecificCard.value"
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
          @tab-changed="this.userCards.form.value.activeTab = $event"
          @clear-selected="this.userCards.clearSelectedCards"
          :active-tab="activeTab"
          @card-clicked="cardSelected($event)"
          :card-items="allCards.searchResults.value"
          :selected-cards="userCards.form.value.selectedCards"
          :user-cards="userCards"
          :loading="allCards.ajaxSearch.value.sending"
          :found-count="allCards.tableData.value.totalRecords"
      />

      <!--      <pagination v-model="page" :records="500" :per-page="25" @paginate="myCallback"/>-->
    </div>

    <!-- Pagination -->
    <div v-show="'found' === activeTab" class="card-pagination py-2">
      <pagination
          itemtype="button"
          :records="allCards.tableData.value.totalRecords"
          v-model="form.page"
          :per-page="perPage"
          @paginate="callback"/>
    </div>
  </div>
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
import type {_AssignTopic, _CardGroup, _Deck, _DeckGroup, _Topic} from "@/interfaces/inter-sp";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import useAllCards from "@/composables/useAllCards";
import SelectedCardsAssign from "@/components/SelectedCardsAssign.vue";
// @ts-ignore
import Pagination from 'v-pagination-3';
import useUserCards from "@/composables/useUserCards";
import UseUserCards from "@/composables/useUserCards";
import Cookies from "js-cookie";
import {TriggerHelper} from "@/classes/TriggerHelper";

export default defineComponent({
  name: 'CardSelector',
  components: {
    SelectedCardsAssign,
    InputEditorB, InputEditor, PickImage, VueMulitiselect, TimeComp, AjaxAction, HoverNotifications,
    Pagination
  },
  props: {
    userCards: {
      type: Object as () => ReturnType<typeof useUserCards>,
      required: true
    },
    allCards: {
      type: Object as () => ReturnType<typeof useAllCards>,
      required: true
    },
    topicToAssign: {
      type: Object as () => _Topic | null,
      required: false,
      default: null
    },
  },
  data() {
    return {
      pageTitle: 'Assign Topics',
      showMain: false,
    }
  },
  setup: (props, ctx) => {
    return {
      decks: useDecks(),
      decksMulti: useDecks(),
      deckGroup: useDeckGroupLists(),
      deckGroupMulti: useDeckGroupLists(),
      searchTags: useTagSearch(),
      basicCard: useBasicCard(),
      gapCard: useGapCard(),
      topics: useTopics(),
      topicsMulti: useTopics(),
      collections: useCollections(),
      // allCards: useAllCards(),
      // userCards: useUserCards()
    }
  },
  computed: {
    cardItems(): _CardGroup[] {
      return this.allCards.searchResults.value;
    },
    activeTab() {
      return this.userCards.form.value.activeTab;
    },
    selectedCards() {
      return this.userCards.form.value.selectedCards;
    },
    form() {
      return this.userCards.form.value;
    },
    perPage() {
      return 50;
    },
  },
  created() {
    console.log('now created');
    jQuery('.all-loading').hide();
    jQuery('.all-loaded').show();
    this.showMain = true;

    this.deckGroupMulti.tableData.value.paginationOptions.perPage = 51;
    this.topics.tableData.value.paginationOptions.perPage = 50;
    // console.log('created now');

    this.topics.load();
    this.deckGroup.load();
    this.decks.search('');
    this.deckGroupMulti.search('');
    this.decksMulti.search('');
    this.topicsMulti.search('');
  },
  methods: {
    UseUserCards,
    spClientData,
    searchCards() {
      this.allCards.search(
          '',
          this.userCards.form.value.group,
          this.userCards.form.value.deck,
          this.userCards.form.value.topic,
          this.userCards.form.value.cardTypes,
          null !== this.topicToAssign ? [this.topicToAssign] : []
      );
    },
    cardSelected(card: _CardGroup) {
      let index = this.userCards.form.value.selectedCards.findIndex((c) => c.id === card.id);
      if (index > -1) {
        this.userCards.removeSelectedCard(card);
      } else {
        this.userCards.oneSpecificCard.value = card; // This will trigger the watch in useUserCards to add the card to the selected cards.
      }
    },
    callback: function (page) {
      this.page = page;
      this.allCards.page.value = page;
      this.searchCards();
      // console.log(`Page ${page} was selected. Do something about it`);
    },
    clearGroup() {
      this.userCards.form.value.group = null;
      this.userCards.form.value.deck = null;
      this.userCards.form.value.topic = null;
      this.decks.search('');
      this.searchCards();
    },
    clearDeck() {
      this.userCards.form.value.deck = null;
      this.userCards.form.value.topic = null;
      this.searchCards();
    },
    clearTopic() {
      this.userCards.form.value.topic = null;
      this.searchCards();
    },
  },
  watch: {
    'userCards.form.value.group': function (newVal, oldVal) {
      const form = this.userCards.form.value;
      form.deck = null;
      form.topic = null;
      this.decksMulti.searchResults.value = [];
      this.topicsMulti.searchResults.value = [];
      this.decksMulti.search('', form.group);
      this.searchCards();
    },
    'userCards.form.value.deck': function (newVal, oldVal) {
      const form = this.userCards.form.value;
      form.topic = null;
      this.topicsMulti.searchResults.value = [];
      this.topicsMulti.search('', form.deck);
      this.searchCards();
    },
    'userCards.form.value.topic': function (newVal, oldVal) {
      // const form = this.userCards.form.value;
      // form.selectedCards = [];
      // this.assignTopic.assign.value.topic = newVal;
      this.searchCards();
    },
    'userCards.form.value.cardTypes': function (newVal, oldVal) {
      this.searchCards();
    },
  },
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->