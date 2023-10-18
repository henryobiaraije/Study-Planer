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
        <!--  Deck -->
        <div v-if="userCards.form.value.group" class="group flex-1 mp-slide-in">
          <span class="text-base flex items-center gap-2 w-full mb-2 font-medium">
            <span>Subject <span class="text-gray-400">(optional)</span></span>
            <span @click="clearDeck"
                  class="hidden px-2 py-1 text-xs bg-gray-300 font-normal group-hover:inline-block cursor-pointer hover:bg-gray-400">Clear</span>
          </span>
          <vue-mulitiselect
              v-model="userCards.form.value.deck"
              :options="decks.searchResults.value"
              :multiple="false"
              :loading="decks.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="(query) => decks.search(query,form.group as any)"
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
              :options="topics.searchResults.value"
              :multiple="false"
              :loading="topics.ajaxSearch.value.sending"
              :searchable="true"
              :allowEmpty="false"
              :close-on-select="true"
              :taggable="false"
              :createTag="false"
              @search-change="(query) => topics.search(query,form.deck as any)"
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
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('basic') ? 'text-sp-500' : '']"
            >
              <span class="block items-center">
                <input type="checkbox" value="basic" v-model="userCards.form.value.cardTypes">
              </span>
              <span class="'block no-break">Basic Cards</span>
            </label>
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('gap') ? 'text-sp-500' : '']"
            >
              <span class="block ">
                <input type="checkbox" value="gap" v-model="userCards.form.value.cardTypes">
              </span>
              <span class="block no-break">Gap Cards</span>
            </label>
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
                   :class="[userCards.form.value.cardTypes.includes('table') ? 'text-sp-500' : '']"
            >
              <span class="block ">
                <input type="checkbox" value="table" v-model="userCards.form.value.cardTypes">
              </span>
              <span class="block">Table Cards</span>
            </label>
            <label class="flex gap-2 items-center justify-start !m-0 cursor-pointer hover:text-sp-500"
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
          :active-tab="activeTab"
          @card-clicked="cardSelected($event)"
          :card-items="cardItems"
          :selected-cards="selectedCards"
          :loading="allCards.ajaxSearch.value.sending"
          :found-count="allCards.tableData.value.totalRecords"
      />
      <!--      <pagination v-model="page" :records="500" :per-page="25" @paginate="myCallback"/>-->
    </div>

    <!-- Pagination -->
    <div v-show="'found' === activeTab" class="card-pagination py-2">
      <pagination itemtype="button" :records="allCards.tableData.value.totalRecords" v-model="form.page" :per-page="10"
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
import type {_AssignTopic, _CardGroup, _Deck, _DeckGroup} from "@/interfaces/inter-sp";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import useAllCards from "@/composables/useAllCards";
import SelectedCardsAssign from "@/components/SelectedCardsAssign.vue";
// @ts-ignore
import Pagination from 'v-pagination-3';
import useUserCards from "@/composables/useUserCards";
import UseUserCards from "@/composables/useUserCards";


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
    }
  },
  data() {
    return {
      pageTitle: 'Assign Topics',
      showMain: false,
      // selectedCards: [] as _CardGroup[],
      // page: 1,
      // activeTab: 'found',
      // whatToDo: 'selected_cards'
    }
  },
  setup: (props, ctx) => {
    return {
      decks: useDecks(),
      deckGroup: useDeckGroupLists(),
      searchTags: useTagSearch(),
      basicCard: useBasicCard(),
      gapCard: useGapCard(),
      topics: useTopics(),
      collections: useCollections(),
      allCards: useAllCards(),
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
    }
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
    UseUserCards,
    spClientData,
    searchCards() {
      this.allCards.search(
          '',
          this.userCards.form.value.group,
          this.userCards.form.value.deck,
          this.userCards.form.value.topic,
          this.userCards.form.value.cardTypes
      );
    },
    cardSelected(card: _CardGroup) {
      this.userCards.oneSpecificCard.value = card;
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
  // watch
  watch: {
    'userCards.form.value.group': function (newVal, oldVal) {
      const form = this.userCards.form.value;
      form.deck = null;
      form.topic = null;
      this.decks.searchResults.value = [];
      this.topics.searchResults.value = [];
      this.decks.search('', form.group);
      this.searchCards();
    },
    'userCards.form.value.deck': function (newVal, oldVal) {
      const form = this.userCards.form.value;
      form.topic = null;
      this.topics.searchResults.value = [];
      this.topics.search('', form.deck);
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