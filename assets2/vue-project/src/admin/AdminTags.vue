<template>
  <!--  Header -->
  <ul class="subsubsub all-loaded w-full p-0">
    <li><h1 class="wp-heading-inline">{{ pageTitle }}</h1></li>
    <li class="all"><a :href="activeUrl" class="" :class="{'text-green-500': !inTrash}" aria-current="page">
      Active <span class="count">({{ tags.totals.value.active }})</span></a> |
    </li>
    <li class="publish" :class="{'text-red-500' : inTrash}"><a :href="trashUrl">
      Trashed <span class="count">({{ tags.totals.value.trashed }})</span></a>
    </li>
  </ul>
  <!--  Body  -->
  <div class="">
    <div class="flex flex-wrap gap-3 px-1 md:px-4">
      <!--    Form   -->
      <div class="form-area flex-1 md:flex-none  md:w-30 ">
        <form @submit.prevent="tags.create()" class="bg-white rounded p-2">
          <label class="tw-simple-input">
            <span class="tw-title">Tag Name</span>
            <input v-model="tags.newName.value" name="tag_name" required type="text">
          </label>
          <ajax-action
              button-text="Create"
              css-classes="button"
              icon="fa fa-plus"
              :ajax="tags.ajaxCreate.value">
          </ajax-action>
        </form>
      </div>
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
            @page-change="tags.onPageChange"
            @sort-change="tags.onSortChange"
            @column-filter="tags.onColumnFilter"
            @per-page-change="tags.onPerPageChange"
            @selected-rows-change="tags.onSelect"
            @search="tags.onSearch"
        >
          <template slot="table-row" #table-row="props">
            <div v-if="props.column.field === 'name'">
              <input @input="tags.onEdit(props.row)" v-model="props.row.name"/>
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
                button-text="Delete Selected Permanently "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="tags.batchDelete()"
                :ajax="tags.ajaxDelete.value">
            </ajax-action-not-form>
            <ajax-action-not-form
                button-text="Trash Selected "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="tags.batchTrash()"
                :ajax="tags.ajaxTrash.value">
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
      pageTitle: 'Tags',
      activeUrl: 'admin.php?page=study-planner-pro-deck-groups',
      trashUrl: 'admin.php?page=study-planner-pro-deck-groups&status=trash',
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
    }
  },
  computed: {
    totalActive() {
      // return this.deckGroups.tableData.value.totalRecords;
      return this.tags.totals.value.active;
    },
    totalTrash() {
      return this.tags.totals.value.trashed;
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
      return this.tags.tableData.value;
    },
  },
  created() {
    jQuery('.all-loading').hide();
    this.tags.loadItems();
    console.log('created now');
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