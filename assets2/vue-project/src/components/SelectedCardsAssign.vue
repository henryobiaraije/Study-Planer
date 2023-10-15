<template>
  <div
      class="tabs flex items-center gap-2 relative border-r-0 border-l-0 border-t-0 border-b border-solid border-sp-400">
    <label :class="[tabClassFound]">
      <input type="radio" name="tab" value="found" v-model="activeTab" style="display: none">
      <span>Found</span>
    </label>
    <label :class="[tabClassSelected]">
      <input type="radio" name="tab" value="selected" v-model="activeTab" style="display: none">
      <span>Selected</span>
    </label>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import type {_Card} from "@/interfaces/inter-sp";

export default defineComponent({
  name: 'SelectedCardsAssign',
  components: {},
  props: {
    foundCards: {
      type: Array as () => _Card[],
      required: true
    },
    selectedCards: {
      type: Array as () => _Card[],
      required: true
    }
  },
  data() {
    return {
      activeTab: 'found' as 'found' | 'selected'
    }
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
    }
  },
  computed: {
    tabClassSelected() {
      return this.tabLabelClass('selected');
    },
    tabClassFound() {
      return this.tabLabelClass('found');
    },
  },
  created() {

  },

});

</script>