<template>
  <div class="user-add-cards pt-2 pb-6">
    <div class="sp-header pb-4 pt-2">
      <h2 class="text-xl font-semibold mb-0 text-sp-500 p-0">
        Add Cards
      </h2>
      <p class="text-base text-gray-500">Here you can add cards to your study deck.</p>
    </div>
    <br/>
    <CardSelector :user-cards="userCards" :all-cards="allCards"/>
    <br/>
    <p>Add the selected cards to your study deck.</p>
    <ajax-action
        :disable="userCards.form.value.selectedCards.length < 1"
        button-text="Add Cards"
        css-classes="button"
        icon="fa fa-save"
        @click="addCards"
        :ajax="userCards.ajaxAddCards.value">
    </ajax-action>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";
import useAllCards from "@/composables/useAllCards";

export default defineComponent({
  name: 'UserDashboardAddCard',
  components: {AjaxAction, CardSelector},
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
  computed: {},
  created() {
    this.allCards.fromFrontend.value = true;
    this.allCards.forAddToStudyDeck.value = true;
  },
  methods: {
    addCards() {
      this.userCards.addCards()
          .then(done => {
            this.allCards.tableData.value.totalRecords = 0;
            this.allCards.total.value = 0;
            this.allCards.tableData.value.rows = [];
            this.userCards.form.value.selectedCards = [];
          });
    }
  }
});

</script>
<!--<style type="scss">-->
//@import "../../_extra/bootstrap-4/scss/_pagination.scss";
<!--</style>-->