<template>
  <div class="user-add-cards pt-2 pb-6">
    <div class="sp-header pb-4 pt-2">
      <h2 class="text-xl font-semibold mb-0 text-sp-500 p-0">
        Remove Cards
      </h2>
      <p class="text-base text-gray-500">Here you can browse through and delete cards in your study deck.</p>
      <p class="text-base text-gray-500"><b>Note: </b>When you remove cards, they are removed from your study deck.
        However, they'll still be available to add again from the "Add Card" tab.</p>
    </div>
    <br/>
    <div class="form-area flex-1 md:flex-none  md:w-30 ">
      <vue-good-table
          :columns="tableDataValue.columns"
          :mode="'remote'"
          :rows="tableDataValue.rows"
          :total-rows="tableDataValue.totalRecords"
          :compact-mode="true"
          :line-numbers="true"
          :is-loading="tableDataValue.isLoading"
          :pagination-options="tableDataValue.paginationOptions"
          :search-options="tableDataValue.searchOptions"
          :sort-options="tableDataValue.sortOption"
          :select-options="{ enabled: true, selectOnCheckboxOnly: true, }"
          :theme="''"
          @page-change="allCards.onPageChange"
          @sort-change="allCards.onSortChange"
          @column-filter="allCards.onColumnFilter"
          @per-page-change="allCards.onPerPageChange"
          @selected-rows-change="allCards.onSelect"
          @search="customSearch"
      >
        <template slot="table-row" #table-row="props">
          <div v-if="props.column.field === 'name'">
            <a :href="props.row.card_group_edit_url" class="pb-2 block"><span>{{ props.row.name }}</span></a>
            <div class="flex gap-2">
              <v-btn
                  color="primary"
                  @click="viewCard(props.row.id)"
                  variant="outlined"
                  size="small"
              >
                View
              </v-btn>
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
                      :show-only-answers="true"
                  />
                </v-card>
              </v-dialog>
              <v-btn
                  color="primary"
                  @click="removeCard([props.row.id])"
                  variant="elevated"
                  size="small"
                  :loading="userCards.ajaxRemoveCard.value.sending && cardGroupIdsRemoving.includes(props.row.id)"
                  :disabled="userCards.ajaxRemoveCard.value.sending && cardGroupIdsRemoving.includes(props.row.id)"
              >
                Remove
              </v-btn>
            </div>
          </div>
          <div v-else-if="props.column.field === 'type'">
            {{ props.row.card_type }}
          </div>
          <div v-else-if="props.column.field === 'cards'">
            {{ props.row.cards.length }} Cards
          </div>
          <div v-else-if="props.column.field === 'deck'">
            {{ props.row.deck ? props.row.deck.name : '' }}
          </div>
          <div v-else-if="props.column.field === 'deck_group'">
            {{ props.row.deck.deck_group ? props.row.deck.deck_group.name : '-' }}
          </div>
          <div v-else-if="props.column.field === 'topic'">
            {{ props.row.topic ? props.row.topic.name : '-' }}
          </div>
          <div v-else-if="props.column.field === 'tags'">
            <ul class="" style="min-width: 100px;">
              <li v-for="(item,itemIndex) in props.row.tags"
                  class="inline-flex items-center justify-center mr-1 px-2 py-1 text-xs font-bold leading-none text-white bg-gray-500 rounded">
                {{ item.name }}
              </li>
            </ul>
          </div>
          <span v-else-if="props.column.field === 'created_at'">
							<time-comp :time="props.row.created_at"></time-comp>
						</span>
          <span v-else-if="props.column.field === 'updated_at'">
							<time-comp :time="props.row.updated_at"></time-comp>
						</span>
          <span v-else>
				      {{ props.formattedRow[props.column.field] }}
				    </span>
        </template>
        <template #selected-row-actions>
          <v-btn
              color="primary"
              @click="removeCard(allCards.tableData.value.selectedRows.map((item) => item.id))"
              variant="elevated"
              size="small"
              :loading="userCards.ajaxRemoveCard.value.sending"
              :disabled="userCards.ajaxRemoveCard.value.sending"
          >
            Remove selected
          </v-btn>
          <!--          <ajax-action-not-form-->
          <!--              v-if="inTrash"-->
          <!--              button-text="Restore Selected  "-->
          <!--              css-classes="button button-secondary"-->
          <!--              icon="fa fa-recycle"-->
          <!--              @click="allCards.batchRestore()"-->
          <!--              :ajax="allCards.ajaxRestore.value">-->
          <!--          </ajax-action-not-form>-->
          <!--          <ajax-action-not-form-->
          <!--              button-text="Delete Selected Permanently "-->
          <!--              css-classes="button button-link-delete"-->
          <!--              icon="fa fa-trash"-->
          <!--              @click="allCards.batchDelete()"-->
          <!--              :ajax="allCards.ajaxDelete.value">-->
          <!--          </ajax-action-not-form>-->
          <!--          <ajax-action-not-form-->
          <!--              v-if="!inTrash"-->
          <!--              button-text="Trash Selected "-->
          <!--              css-classes="button button-link-delete"-->
          <!--              icon="fa fa-trash"-->
          <!--              @click="allCards.batchTrash()"-->
          <!--              :ajax="allCards.ajaxTrash.value">-->
          <!--          </ajax-action-not-form>-->
        </template>
      </vue-good-table>
    </div>
  </div>
  <div class="sp-modal" v-if="cardsToView.length > 0">

  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";
import TimeComp from "@/vue-component/TimeComp.vue";
import AjaxActionNotForm from "@/vue-component/AjaxActionNotForm.vue";
import HoverNotifications from "@/vue-component/HoverNotifications.vue";
import Multiselect from "vue-multiselect";
// @ts-ignore
import {VueGoodTable} from 'vue-good-table-next';
// import the styles
import 'vue-good-table-next/dist/vue-good-table-next.css'
import useTagSearch from "@/composables/useTagSearch";
import useNewDeckGroup from "@/composables/useNewDeckGroup";
import useTags from "@/composables/useTags";
import useAllCards from "@/composables/useAllCards";
import useAllNewRemoveCards from "@/composables/useAllNewRemoveCards";
import type {_Card, _CardGroup} from "@/interfaces/inter-sp";
import QuestionModal from "@/vue-component/QuestionModal.vue";
import useImageCard from "@/composables/useImageCard";

export default defineComponent({
  name: 'UserDashboardNewCards',
  components: {
    QuestionModal,
    TimeComp,
    AjaxActionNotForm,
    HoverNotifications,
    'ajax-action': AjaxAction,
    'vue-mulitiselect': Multiselect,
    'hover-notifications': HoverNotifications,
    'vue-good-table': VueGoodTable,
  },
  props: {},
  data() {
    return {
      pageTitle: 'All Cards',
      activeUrl: 'admin.php?page=study-planner-pro-deck-cards',
      trashUrl: 'admin.php?page=study-planner-pro-deck-cards&status=trash',
      cardsToView: [] as _Card[],
      modalOpenQuestion: null,
      viewDialog: false,
      cardGroupIdsRemoving: [] as number[],
    }
  },
  setup: (props, ctx) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const status = searchParams.get('status');

    return {
      // decks: useDecks(status),
      // deckGroups: useDeckGroupLists(status),
      tagSearch: useTagSearch(),
      newDeckGroup: useNewDeckGroup(),
      searchTags: useTagSearch(),
      tags: useTags(status),
      allCards: useAllNewRemoveCards(),
      userCards: useUserCards(),
      imageCard: useImageCard(),
    }
  },
  computed: {
    totalActive() {
      // return this.deckGroups.tableData.value.totalRecords;
      return this.allCards.totals.value.active;
    },
    totalTrash() {
      return this.allCards.totals.value.trashed;
    },
    inTrash() {
      let url = new URL(window.location.href);
      let status = url.searchParams.get("status");
      return status === 'trash';
    },
    // deckNew() {
    //   return this.decks.newItem.value;
    // },
    tableDataValue() {
      return this.allCards.tableData.value;
    },
  },
  created() {
    jQuery('.all-loading').hide();
    this.allCards.fromFrontend.value = true;
    this.allCards.forRemoveFromStudyDeck.value = true;
    this.allCards.search(
        '',
        null,
        null,
        null,
        [],
        1000000 // Load all cards.
    );
  },
  methods: {
    createDeckGroup() {
      this.tags.create();
    },
    customSearch(query: { searchTerm: string }) {
      const searchFor = query.searchTerm;
      console.log({query, searchFor})
      this.allCards.search(searchFor);
    },
    viewCard(cardGroupId: number): _CardGroup[] {
      console.log({cardGroupId});
      this.cardsToView = this.allCards.tableData.value.rows.find((item: _CardGroup) => item.id === cardGroupId).cards;
      this.viewDialog = true;
      // this.cardsToView = cardGroup.cards;
      // setTimeout(() => this.openQuestionModal(), 1000);
    },
    removeCard(cardGroupIds: number[]) {
      // push without duplicates.
      this.cardGroupIdsRemoving.push(...cardGroupIds.filter((item) => !this.cardGroupIdsRemoving.includes(item)));

      this.userCards.removeCard(cardGroupIds)
          .then((done) => {
            this.cardGroupIdsRemoving = this.cardGroupIdsRemoving.filter((item) => !cardGroupIds.includes(item));
            this.allCards.removeCardsFromResults(cardGroupIds);
          })
          .catch((err) => {
            this.cardGroupIdsRemoving = this.cardGroupIdsRemoving.filter((item) => !cardGroupIds.includes(item));
          });
    },
  }
});

</script>

<style>
.v-model {
  z-index: 9999999999 !important;
}
</style>