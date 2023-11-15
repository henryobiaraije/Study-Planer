<template>
  <!--  <form @submit.prevent="">-->
  <!--  Tabs Found, Selected -->
  <div
      class="tabs group flex items-center gap-2 relative border-r-0 border-l-0 border-t-0 border-b border-solid border-sp-400">
    <label :class="[tabClassFound]" class="cursor-pointer">
      <input type="radio" name="tab" value="found" @change="$emit('tab-changed','found')"
             style="display: none">
      <span class="font-semibold text-sp-900">
        Found
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ foundCount }})</span>
      </span>
    </label>
    <label :class="[tabClassSelected]" class="cursor-pointer">
      <input type="radio" name="tab" value="selected" @change="$emit('tab-changed','selected')" style="display: none">
      <span class="font-semibold text-sp-900">
        Selected
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ selectedCount }})</span>
      </span>
    </label>
    <label @click="clearSelected" :class="['hidden group-hover:block bg-gray-100 py-1 px-2']" class="cursor-pointer">
      <span class="font-semibold text-sp-900">
        Clear Selected
      </span>
    </label>
  </div>
  <!--  Cards List  -->
  <div class="cards">
    <ul class="card-wrapper !list-none !p-0 !m-0 max-h-300px overflow-y-auto shadow">
      <li v-for="(cardGroup,cardIndex) in cardsToDisplay"
          class="!p-0 !m-0 justify-between items-center hover:bg-sp-50 border-b border-solid border-sp-300 cursor-pointer"
          key="cardGroup.id"
      >
        <label
            class="flex single-card cursor-pointer group px-2 flex-1 gap-2 justify-between items-center ">
          <div class="flex-initial icon block icon p-2 hover:bg-sp-300 rounded-full "
               @click="$emit('card-clicked', cardGroup)"
          >
            <!-- Plus icon -->
            <svg v-if="!selectedCardIds.includes(cardGroup.id)" class="w-[20px] h-[20px]" fill="none"
                 stroke="currentColor"
                 stroke-width="3.5"
                 viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
            <!-- Checked icon -->
            <svg v-else class="w-[20px] h-[20px] text-sp-500" fill="none" stroke="currentColor" stroke-width="3.5"
                 viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M4.5 12.75l6 6 9-13.5"></path>
            </svg>
          </div>
          <div class="body-details flex-auto lg:flex">
            <div @click="viewCard(cardGroup.id)" class="card-name text-base font-medium block ">{{
                cardGroup.name
              }}
            </div>
            <div class="in-mobile flex lg:hidden gap-2 card-stats bg-gray-50">
              <div class="mobile-stats flex justify-between gap-4 border-t-sm border-solid border-gray-400 ">
                <div class="flex flex-row gap-1 items-center">
                  <span class="text-sm text-gray-400">Card Type: </span>
                  <v-chip
                      class="ma-2"
                      size="x-small"
                      color="primary"
                  >
                    {{ cardGroup.card_type }}
                  </v-chip>
                </div>
                <div class="flex flex-row gap-1 items-center">
                  <span class="text-sm text-gray-400">Cards: </span>
                  <v-chip
                      class="ma-2"
                      size="x-small"
                      color="primary"
                  >
                    {{ cardGroup.cards.length }}
                  </v-chip>
                </div>
              </div>
            </div>
            <div class="in-desktop hidden lg:flex">
              <v-chip
                  class="ma-2 text-xs my-0"
                  color="primary"
                  size="x-small"
              >
                {{ cardGroup.card_type }}
              </v-chip>
              <v-chip
                  class="ma-2 text-xs my-0"
                  color="primary"
                  size="x-small"
              >
                {{ cardGroup.cards.length }} Cards
              </v-chip>
            </div>
          </div>
        </label>
        <v-dialog
            v-model="viewDialog"
            width="auto"
        >
          <v-card>
            <v-card-actions>
              <div class="flex flex-row justify-between items-center w-full">
                <span class="flex-1 text-xl !font-bold">Cards</span>
                <span class="flex-initial">
                          <v-btn color="primary" block @click="viewDialog = false">Close</v-btn>
                        </span>
              </div>
            </v-card-actions>
            <QuestionModal
                title="Cards"
                :cards="cardsToView"
                :index-to-start="carouselCardIndexToStart"
                :show-only-answers="true"
                :user-cards="userCards"
                @card-selected="(cardGroupId:number) => $emit('card-clicked', cardItems.find((item) => item.id === cardGroupId))"
                :for-add-cards="true"
            />
          </v-card>
        </v-dialog>
      </li>
    </ul>
  </div>
  <!--  Tabs Found, Selected -->
  <div
      v-if="cardsToDisplay.length"
      class="tabs flex items-center gap-2 relative border-r-0 border-l-0 border-t-0 border-b border-solid border-sp-400">
    <label :class="[tabClassFound]" class="cursor-pointer">
      <input type="radio" name="tab" value="found" @change="$emit('tab-changed','found')"
             style="display: none">
      <span class="font-semibold text-sp-900">
        Found
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ foundCount }})</span>
      </span>
    </label>
    <label :class="[tabClassSelected]" class="cursor-pointer">
      <input type="radio" name="tab" value="selected" @change="$emit('tab-changed','selected')" style="display: none">
      <span class="font-semibold text-sp-900">
        Selected
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ selectedCount }})</span>
      </span>
    </label>
  </div>
  <!--  </form>-->

</template>
<script lang="ts">

import {defineComponent} from "vue";
import type {_CardGroup} from "@/interfaces/inter-sp";
import QuestionModal from "@/vue-component/QuestionModal.vue";
import {_Card} from "@/interfaces/inter-sp";
import useUserCards from "@/composables/useUserCards";
import useMyStore from "@/composables/useMyStore";
import useWidth from "@/composables/useWidth";

export default defineComponent({
  name: 'SelectedCardsAssign',
  components: {QuestionModal},
  emits: ['card-clicked', 'tab-changed', "clear-selected"],
  props: {
    cardItems: {
      type: Array as () => _CardGroup[],
      required: true
    },
    selectedCards: {
      type: Array as () => _CardGroup[],
      required: true
    },
    loading: {
      type: Boolean,
      required: true,
      default: false
    },
    activeTab: {
      type: String as () => 'found' | 'selected',
      required: true,
      default: 'found'
    },
    foundCount: {
      type: Number,
      required: true,
      default: 0
    },
    userCards: {
      type: Object as () => ReturnType<typeof useUserCards>,
      required: true
    },
  },
  data() {
    return {
      cardsToView: [] as _Card[],
      viewDialog: false,
      /**
       * So that we can set the card to show first in the question carousel when in AddCards page.
       */
      carouselCardIndexToStart: 0,
    }
  },
  setup: (props, ctx) => {
    return {
      myStore: useMyStore(),
      uWidth: useWidth(),
    }
  },
  methods: {
    clearSelected() {
      this.$emit('clear-selected');
    },
    viewCard(cardGroupId: number): _CardGroup[] {

      if (this.myStore.store.inAddCards) {
        // Display all cards in all groups if in AddCards page.
        let cartsCountTillToStart = 0;
        let stopCounting = false;
        const cards: _Card[] = [];

        this.cardItems.forEach(group => {

          if (cardGroupId === group.id) {
            stopCounting = true;
          }

          if (stopCounting) {
            // Continue to increase the index to start until we reach the current group.
            cartsCountTillToStart += group.cards.length;
          }

          group.cards.forEach(card => cards.push(card));
        });

        this.carouselCardIndexToStart = cartsCountTillToStart - 1;
        this.cardsToView = cards;
      } else {
        this.cardsToView = this.cardItems.find((item: _CardGroup) => item.id === cardGroupId).cards;
      }
      this.viewDialog = true;
    },
    tabLabelClass(tab: 'found' | 'selected') {
      const activeClasses =
          [
            'bottom-[-1px] border-t border-l border-r border-sp-400 ',
            'border-b-sp-wp-bg border-solid ',
            'relative bg-sp-wp-bg ',
            'py-2 px-2',
            'text-bold'
          ].join(' ');
      const inActiveClasses = ['bg-sp-400 py-1 px-2'].join(' ');
      const isActive = tab === this.activeTab;
      return {
        [activeClasses]: isActive,
        [inActiveClasses]: !isActive,
      }
    },
  },
  computed: {
    tabClassSelected() {
      return this.tabLabelClass('selected');
    },
    tabClassFound() {
      return this.tabLabelClass('found');
    },
    selectedCount() {
      return this.selectedCards.length;
    },
    cardsToDisplay(): _CardGroup[] {
      return this.activeTab === 'found' ? this.cardItems : this.selectedCards;
    },
    selectedCardIds(): number[] {
      return this.selectedCards.map((card: _CardGroup) => card.id);
    }
  },
  created() {

  },

});

</script>