<template>
  <div class="user-add-cards pt-2 pb-6">
    <div class="sp-header pb-4 pt-2">
      <h2 class="text-xl font-semibold mb-0 text-sp-500 p-0">
        Add Cards
      </h2>
      <p class="text-base text-gray-500">Here you can add cards to your study deck.</p>
    </div>
    <br/>
    <CardSelector/>
    <br/>
    <p>Add the selected cards to your study deck.</p>
    <ajax-action
        button-text="Add Cards"
        css-classes="button"
        icon="fa fa-save"
        @click="userCards.addCards"
        :ajax="userCards.ajaxAddCards.value">
    </ajax-action>
  </div>

</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";

export default defineComponent({
  name: 'UserDashboardAddCard',
  components: {AjaxAction, CardSelector},
  props: {},
  data() {
    return {}
  },
  setup: (props, ctx) => {
    return {
      userCards: useUserCards()
    }
  },
  computed: {},
  created() {
  },
  methods: {}
});

</script>