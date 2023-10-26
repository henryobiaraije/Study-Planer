<template>
  <div class="user-study-deck pt-2 pb-6">
    <v-progress-linear v-if="userCards.ajaxLoadUserCard.value.sending" color="primary"
                       indeterminate></v-progress-linear>
    <template v-for="(group,groupIndex) in userDeckGroups">
      <AccordionItem :user-cards="userCards" :item="group" :top="true">
      </AccordionItem>
    </template>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";
import useAllCards from "@/composables/useAllCards";
import type {_DeckGroup} from "@/interfaces/inter-sp";
import AccordionItem from "@/admin/AccordionItem.vue";

export default defineComponent({
  name: 'UserDashboardStudyDeck',
  components: {AccordionItem, AjaxAction, CardSelector},
  props: {},
  data() {
    return {}
  },
  setup: (props, ctx) => {
    return {
      userCards: useUserCards(),
      allCards: useAllCards(),
    }
  },
  computed: {
    userDeckGroups(): _DeckGroup[] {
      return this.userCards.userDeckGroups.value ?? [];
    }
  },
  created() {
    this.userCards.loadUserCards();
  },
  methods: {
    toggle(cssClass: string) {

    }
  }
});

</script>
