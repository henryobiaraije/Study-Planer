<template>
  <!--  Header -->
  <ul class="subsubsub all-loaded w-full p-0">
    <li><h1 class="wp-heading-inline">{{ pageTitle }}</h1></li>
    <li class="all"><a :href="activeUrl" class="" :class="{'text-green-500': !inTrash}" aria-current="page">
      Active <span class="count">({{ allCards.totals.value.active }})</span></a> |
    </li>
    <li class="publish" :class="{'text-red-500' : inTrash}"><a :href="trashUrl">
      Trashed <span class="count">({{ allCards.totals.value.trashed }})</span></a>
    </li>
  </ul>
  <!--  Body  -->
  <div class="">
    <div class="flex flex-wrap gap-3 px-1 md:px-4">
      <!--    Table   -->
      <div class="table-area flex-1">
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
            @search="allCards.onSearch"
        >
          <template slot="table-row" #table-row="props">
            <div v-if="props.column.field === 'name'">
              <a :href="props.row.card_group_edit_url"><span>{{ props.row.name }}</span></a>
              <div v-if="!inTrash" class="row-actions">
									<span class="edit">
									<a class="text-blue-500 font-bold" :href="props.row.card_group_edit_url">
									Edit <i class="fa fa-pen-alt"></i></a>  </span>
              </div>
            </div>
            <div v-else-if="props.column.field === 'type'">
              {{ props.row.card_type }}
            </div>
            <div v-else-if="props.column.field === 'total_cards'">
              {{ props.row.cards_count }}
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
            <ajax-action-not-form
                v-if="inTrash"
                button-text="Restore Selected  "
                css-classes="button button-secondary"
                icon="fa fa-recycle"
                @click="allCards.batchRestore()"
                :ajax="allCards.ajaxRestore.value">
            </ajax-action-not-form>
            <ajax-action-not-form
                button-text="Delete Selected Permanently "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="allCards.batchDelete()"
                :ajax="allCards.ajaxDelete.value">
            </ajax-action-not-form>
            <ajax-action-not-form
                v-if="!inTrash"
                button-text="Trash Selected "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="allCards.batchTrash()"
                :ajax="allCards.ajaxTrash.value">
            </ajax-action-not-form>
          </template>
        </vue-good-table>
      </div>
    </div>
  </div>
  <br/>
  <hover-notifications></hover-notifications>
</template>
<script lang="ts">
import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import Multiselect from 'vue-multiselect'
import HoverNotifications from "@/vue-component/HoverNotifications.vue";
import AjaxActionNotForm from "@/vue-component/AjaxActionNotForm.vue";
import TimeComp from "@/vue-component/TimeComp.vue";
// @ts-ignore
import {VueGoodTable} from 'vue-good-table-next';
// import the styles
import 'vue-good-table-next/dist/vue-good-table-next.css'
import useDecks from "@/composables/useDecks";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import useTagSearch from "@/composables/useTagSearch";
import useNewDeckGroup from "@/composables/useNewDeckGroup";
import useTags from "@/composables/useTags";
import useAllCards from "@/composables/useAllCards";

export default defineComponent({
  name: 'AdminTags',
  components: {
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
      activeUrl: 'admin.php?page=study-planner-deck-cards',
      trashUrl: 'admin.php?page=study-planner-deck-cards&status=trash',
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
      allCards: useAllCards(status),
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
    this.allCards.loadItems();
  },
  methods: {
    createDeckGroup() {
      this.tags.create();
    },
  }
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->