<template>
  <div class="user-study-deck pt-2 pb-6">
    <v-progress-linear v-if="userCards.ajaxLoadUserCard.value.sending" color="primary"
                       indeterminate></v-progress-linear>
    <template v-if="!userCards.ajaxLoadUserCard.value.sending && userDeckGroups.length === 0 && !userCards.loadedOnce">
      <div class="mp-slide-in">
        <v-alert type="warning" dismissible>
          Please add cards to your deck on the "Add Cards" tab.
        </v-alert>
      </div>
    </template>
    <!--    <template v-if="!userCards.ajaxLoadUserCard.value.sending && userDeckGroups.length > 0">-->
    <template v-if="userDeckGroups.length > 0">
      <template v-for="(group,groupIndex) in userDeckGroups">
        <div class="mp-slide-in">
          <AccordionItem :user-cards="userCards" :top="true" :current-item="group"/>
        </div>
      </template>
    </template>
    <v-progress-linear
        v-if="userCards.ajaxLoadUserCard.value.sending && userDeckGroups.length > 0" color="primary"
        indeterminate></v-progress-linear>
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
    },
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
