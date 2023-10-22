<template>
  <!--  Header -->
  <ul class="subsubsub all-loaded w-full p-0">
    <li><h1 class="wp-heading-inline">{{ pageTitle }}</h1></li>
    <li class="all"><a :href="activeUrl" class="" :class="{'text-green-500': !inTrash}" aria-current="page">
      Active <span class="count">({{ totalActive }})</span></a> |
    </li>
    <li class="publish" :class="{'text-red-500' : inTrash}"><a :href="trashUrl">
      Trashed <span class="count">({{ totalTrash }})</span></a>
    </li>
  </ul>
  <!--  Body  -->
  <div class="">
    <div class="flex flex-wrap gap-3 px-1 md:px-4">
      <!--    Form   -->
      <div class="form-area flex-1 md:flex-none  md:w-30 ">
        <form @submit.prevent="createDeckGroup()" class="bg-white rounded p-2">
          <label class="tw-simple-input">
            <span class="tw-title">Deck group name</span>
            <input v-model="newDeckGroup.groupName.value" name="deck_group" required type="text">
          </label>
          <div>
            <span>Tags</span>
            <vue-mulitiselect
                v-model="newDeckGroup.newTags.value"
                :options="searchTags.results.value"
                :multiple="true"
                :loading="searchTags.ajax.value.sending"
                :searchable="true"
                :close-on-select="true"
                :taggable="true"
                :createTag="false"
                @tag="searchTags.addTag"
                @search-change="searchTags.search"
                placeholder="Tags"
                label="name"
                track-by="id"
            ></vue-mulitiselect>
          </div>
          <br/>
          <hr/>
          <br/>
          <ajax-action
              button-text="Create"
              css-classes="button"
              icon="fa fa-plus"
              :ajax="newDeckGroup.ajax.value">
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
            @page-change="deckGroups.onPageChange"
            @sort-change="deckGroups.onSortChange"
            @column-filter="deckGroups.onColumnFilter"
            @per-page-change="deckGroups.onPerPageChange"
            @selected-rows-change="deckGroups.onSelect"
            @search="deckGroups.onSearch"
        >
          <template slot="table-row" #table-row="props">
            <div v-if="props.column.field === 'name'">
              <input @input="deckGroups.onEdit(props.row)"
                     :disabled="props.row.name === 'Uncategorized' || inTrash"
                     v-model="props.row.name"/>
              <div v-if="inTrash" class="row-actions">
									<span class="edit">
										<a @click.prevent="deckGroups.openEditModal(props.row,'#modal-edit')"
                       class="text-blue-500 font-bold" href="#">
											Edit <i class="fa fa-pen-alt"></i></a>  </span>
              </div>
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

export default defineComponent({
  name: 'AdminTopics',
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
      pageTitle: 'Deck Groups',
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
      deckGroups: useDeckGroupLists(status),
      tagSearch: useTagSearch(),
      newDeckGroup: useNewDeckGroup(),
      searchTags: useTagSearch(),
    }
  },
  computed: {
    totalActive() {
      // return this.deckGroups.tableData.value.totalRecords;
      return this.deckGroups.totals.value.active;
    },
    totalTrash() {
      return this.deckGroups.totals.value.trashed;
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
      return this.deckGroups.tableData.value;
    },
    deckGroupToEdit() {
      return this.deckGroups.deckGroupToEdit.value;
    }
  },
  created() {
    jQuery('.all-loading').hide();
    this.deckGroups.loadItems();
    console.log('created now');
  },
  methods: {
    createDeckGroup() {
      this.newDeckGroup.xhrCreateNewDeckGroup();
    },
  }
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->