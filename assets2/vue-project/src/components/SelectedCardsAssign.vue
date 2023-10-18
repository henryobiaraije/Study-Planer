<template>
<!--  <form @submit.prevent="">-->
    <!--  Tabs Found, Selected -->
    <div
        class="tabs flex items-center gap-2 relative border-r-0 border-l-0 border-t-0 border-b border-solid border-sp-400">
      <label :class="[tabClassFound]">
        <input type="radio" name="tab" value="found" @change="$emit('tab-changed','found')"
               style="display: none">
        <span class="font-semibold text-sp-900">
        Found
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ foundCount }})</span>
      </span>
      </label>
      <label :class="[tabClassSelected]">
        <input type="radio" name="tab" value="selected" @change="$emit('tab-changed','selected')" style="display: none">
        <span class="font-semibold text-sp-900">
        Selected
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ selectedCount }})</span>
      </span>
      </label>
    </div>
    <div class="cards">
      <ul class="card-wrapper !list-none !p-0 !m-0 max-h-300px overflow-y-auto shadow">
        <li v-for="(cardGroup,cardIndex) in cardsToDisplay"
            class="flex !p-0 !m-0 justify-between items-center hover:bg-sp-50 border-b border-solid border-sp-300 cursor-pointer"
            key="cardGroup.id"
        >
          <label
              @click="$emit('card-clicked', cardGroup)"
              class="single-card px-2 flex-1 flex gap-2 justify-start items-center py-2 ">
            <!--          <input type="checkbox" :value="cardGroup"-->
            <!--                 @change="cardSelected"-->
            <!--                 v-model="selectedCards">-->
            <span class="block icon">
            <!-- Plus icon -->
            <svg v-if="!selectedCardIds.includes(cardGroup.id)" class="w-[20px] h-[20px]" fill="none"
                 stroke="currentColor"
                 stroke-width="3.5"
                 viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
              <!-- Checked icon -->
            <svg v-else class="w-[20px] h-[20px] text-sp-500" fill="none" stroke="currentColor" stroke-width="3.5"
                 viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
            </svg>
          </span>
            <span class="card-name block">{{ cardGroup.name }}</span>
            <span class="bg-gray-300 px-2 py-1 rounded-md">{{ cardGroup.card_type }}</span>
          </label>
          <!--        <svg class="w-6 h-6 text-gray-400 hover:text-black hover:cursor-pointer" xmlns="http://www.w3.org/2000/svg"-->
          <!--             fill="none" viewBox="0 0 24 24" stroke-width="1.5"-->
          <!--             stroke="currentColor">-->
          <!--          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>-->
          <!--        </svg>-->
        </li>
      </ul>
    </div>
    <!--  Tabs Found, Selected -->
    <div
        v-if="cardsToDisplay.length"
        class="tabs flex items-center gap-2 relative border-r-0 border-l-0 border-t-0 border-b border-solid border-sp-400">
      <label :class="[tabClassFound]">
        <input type="radio" name="tab" value="found" @change="$emit('tab-changed','found')"
               style="display: none">
        <span class="font-semibold text-sp-900">
        Found
        <span v-if="loading" class="w-[20px] h-[20px] text-sp-500">
          <i class="fa fa-spin fa-spinner"></i></span>
        <span v-if="!loading">({{ foundCount }})</span>
      </span>
      </label>
      <label :class="[tabClassSelected]">
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

export default defineComponent({
  name: 'SelectedCardsAssign',
  components: {},
  emits: ['card-clicked', 'tab-changed'],
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
    }
  },
  data() {
    return {}
  },
  setup: (props, ctx) => {
    return {}
  },
  methods: {
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