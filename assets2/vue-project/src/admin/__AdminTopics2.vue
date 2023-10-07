<template type="html">
  <ul class="subsubsub all-loaded w-full p-0">
    <li><h1 class="wp-heading-inline">{{ pageTitle }}</h1></li>
    <li class="all"><a :href="activeUrl" class="" :class="{'text-green-500': !inTrash}" aria-current="page">
      Active <span class="count">({{ totalActive }})</span></a> |
    </li>
    <li class="publish" :class="{'text-red-500' : inTrash}"><a :href="trashUrl">
      Trashed <span class="count">({{ totalTrash }})</span></a>
    </li>
  </ul>
  <br/>
  <div class=" all-loaded" style="display: none;">
    <div class="flex flex-wrap gap-3 px-1 md:px-4">
      <div v-if="!inTrash" class="form-area flex-1 md:flex-none  md:w-30 ">
        <form @submit.prevent="decks.create()" class="bg-white rounded p-2">
          <label class="tw-simple-input">
            <span class="tw-title">Deck </span>
            <input v-model="deckNew.name" name="deck" required type="text">
          </label>
          <div>
            <span>Deck Group</span>
            <multiselect
                v-model="deckNew.deckGroup"
                :options="deckGroups.searchResults.value"
                :multiple="false"
                :loading="deckGroups.ajaxSearch.value.sending"
                :searchable="true"
                :allowEmpty="false"
                :close-on-select="true"
                :taggable="false"
                :createTag="false"
                @search-change="deckGroups.search"
                placeholder="Deck Group"
                label="name"
                track-by="id"
            ></multiselect>
            <!--            <multiselect v-model="value" :options="options" :searchable="false" :close-on-select="false" :show-labels="false" placeholder="Pick a value"></multiselect>-->
          </div>
          <div class="mt-2">
            <span>Tags</span>
            <multiselect
                v-model="deckNew.tags"
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
            ></multiselect>
          </div>
          <div class="mt-3">
            <ajax-action
                button-text="Create"
                css-classes="button"
                icon="fa fa-plus"
                :ajax="decks.ajaxCreate.value">
            </ajax-action>
          </div>
        </form>
      </div>
      <div class="table-area flex-1">
        Table goes here..

        <!--        <vue-good-table-->
        <!--            :columns="tableDataValue.columns"-->
        <!--            :mode="'remote'"-->
        <!--            :rows="tableDataValue.rows"-->
        <!--            :total-rows="tableDataValue.totalRecords"-->
        <!--            :compact-mode="true"-->
        <!--            :line-numbers="true"-->
        <!--            :is-loading="tableDataValue.isLoading"-->
        <!--            :pagination-options="tableDataValue.paginationOptions"-->
        <!--            :search-options="tableDataValue.searchOptions"-->
        <!--            :sort-options="tableDataValue.sortOption"-->
        <!--            :select-options="{ enabled: true, selectOnCheckboxOnly: true, }"-->
        <!--            :theme="''"-->
        <!--            @on-page-change="decks.onPageChange"-->
        <!--            @on-sort-change="decks.onSortChange"-->
        <!--            @on-column-filter="decks.onColumnFilter"-->
        <!--            @on-per-page-change="decks.onPerPageChange"-->
        <!--            @on-selected-rows-change="decks.onSelect"-->
        <!--            @on-search="decks.onSearch"-->
        <!--        >-->
        <!--          <template slot="table-row" slot-scope="props">-->
        <!--            <div v-if="props.column.field === 'name'">-->
        <!--              <input-->
        <!--                  @input="decks.onEdit(props.row)"-->
        <!--                  :disabled="props.row.name === 'Uncategorized' || inTrash"-->
        <!--                  v-model="props.row.name"-->
        <!--              />-->
        <!--              <div v-if="!inTrash" class="row-actions">-->
        <!--									<span class="edit">-->
        <!--									<a @click.prevent="decks.openEditModal(props.row,'#modal-edit')" class="text-blue-500 font-bold"-->
        <!--                     href="#">-->
        <!--									Edit <i class="fa fa-pen-alt"></i></a></span>-->
        <!--              </div>-->
        <!--            </div>-->
        <!--            <div v-else-if="props.column.field === 'deck_group'">-->
        <!--              {{ props.row.deck_group ? props.row.deck_group.name : '' }}-->
        <!--            </div>-->
        <!--            <div v-else-if="props.column.field === 'tags'">-->
        <!--              <ul class="" style="min-width: 100px;">-->
        <!--                <li v-for="(item,itemIndex) in props.row.tags"-->
        <!--                    class="inline-flex items-center bg-gray-500 justify-center mr-1 px-2 py-1 text-xs font-bold leading-none text-white bg-gray-500 rounded">-->
        <!--                  {{ item.name }}-->
        <!--                </li>-->
        <!--              </ul>-->
        <!--            </div>-->
        <!--            <span v-else-if="props.column.field === 'created_at'">-->
        <!--							<time-comp :time="props.row.created_at"></time-comp>-->
        <!--						</span>-->
        <!--            <span v-else-if="props.column.field === 'updated_at'">-->
        <!--							<time-comp :time="props.row.updated_at"></time-comp>-->
        <!--						</span>-->
        <!--            <span v-else>-->
        <!--				      {{ props.formattedRow[props.column.field] }}-->
        <!--				    </span>-->
        <!--          </template>-->
        <!--					<template slot="table-row" slot-scope="props" >-->
        <!--						<input @input="tableOnEdit(props.row)" v-if="props.column.field === 'name'" v-model="props.row.name" />-->
        <!--						<input @input="tableOnEdit(props.row)" v-else-if="props.column.field ==='endpoint'" v-model="props.row.endpoint" />-->
        <!--					</template >-->
        <!--          <div slot="selected-row-actions">-->
        <!--            <?php if ( $in_trash ): ?>-->
        <!--            <ajax-action-not-form-->
        <!--                button-text="Delete Selected Permanently "-->
        <!--                css-classes="button button-link-delete"-->
        <!--                icon="fa fa-trash"-->
        <!--                @click="decks.batchDelete()"-->
        <!--                :ajax="decks.ajaxDelete.value">-->
        <!--            </ajax-action-not-form>-->
        <!--            <?php else: ?>-->
        <!--            <ajax-action-not-form-->
        <!--                button-text="Trash Selected "-->
        <!--                css-classes="button button-link-delete"-->
        <!--                icon="fa fa-trash"-->
        <!--                @click="decks.batchTrash()"-->
        <!--                :ajax="decks.ajaxTrash.value">-->
        <!--            </ajax-action-not-form>-->
        <!--            <?php endif; ?>-->
        <!--          </div>-->
        <!--        </vue-good-table>-->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Multiselect from 'vue-multiselect';
// import "vue-multiselect/dist/vue-multiselect.min.css";
import {computed, onMounted, ref} from "vue";
import useDecks from "@/composables/useDecks";
import jQuery from "jquery";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import useTagSearch from "@/composables/useTagSearch";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import type {_Endpoint} from "@/interfaces/inter-sbe";
import Cookies from "js-cookie";
import {VueGoodTable} from 'vue-good-table';

// import the styles


const pageTitle = 'Topics';
const activeUrl = 'admin.php?page=study-planner-topics';
const trashUrl = 'admin.php?page=study-planner-topics&status=trash';

// <editor-fold desc="Composable">
const decks = useDecks();
const deckGroups = useDeckGroupLists();
const searchTags = useTagSearch();

// </editor-fold desc="Composable">

// const value = ref('');
// const options = ['Select option', 'options', 'selected', 'multiple', 'label', 'searchable', 'clearOnSelect', 'hideSelected', 'maxHeight', 'allowEmpty', 'showLabels', 'onChange', 'touched']

// <editor-fold desc="Computed">

defineProps({
  name: {
    type: String,
    required: false
  }
});
const tableData = {
  columns: [
    {
      label: 'Name',
      field: 'name',
      tooltip: 'Endpoint Name',
    },
    {
      label: 'Created At',
      field: 'created_at',
    },
    {
      label: 'Updated At',
      field: 'updated_at',
    },
  ],
  rows: [],
  isLoading: true,
  totalRecords: 0,
  totalTrashed: 0,
  serverParams: {
    columnFilters: {},
    sort: {
      created_at: '',
      modified_at: '',
    },
    page: 1,
    perPage: 10
  },
  paginationOptions: {
    enabled: true,
    mode: 'page',
    perPage: Cookies.get('alfPerPage') ? Number(Cookies.get('alfPerPage')) : 2,
    position: 'bottom',
    perPageDropdown: [2, 5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 300, 400, 500, 600, 700],
    dropdownAllowAll: true,
    setCurrentPage: 1,
    nextLabel: 'next',
    prevLabel: 'prev',
    rowsPerPageLabel: 'Rows per page',
    ofLabel: 'of',
    pageLabel: 'page', // for 'pages' mode
    allLabel: 'All',
  },
  searchOptions: {
    enabled: true,
    trigger: '', // can be "enter"
    skipDiacritics: true,
    placeholder: 'Search links',
  },
  sortOption: {
    enabled: false,
  },
  //
  post_status: 'publish',
  selectedRowsToDelete: [] as Array<_Endpoint>,
  searchKeyword: '',
};

const totalActive = computed(() => {
  return 0;
});
const totalTrash = computed(() => {
  return 0;
});
const inTrash = computed(() => {
  let url = new URL(window.location.href);
  let status = url.searchParams.get("status");
  return status === 'trash';
});
const deckNew = computed(() => {
  return decks.newItem.value;
})
const tableDataValue = computed(() => {
  return decks.tableData.value;
});

// </editor-fold desc="Computed">

onMounted(() => {
  jQuery('.all-loading').hide();
  jQuery('.all-loaded').show();
  deckGroups.load();
});

</script>

<style scoped lang="scss">

</style>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style src="vue-good-table/dist/vue-good-table.css"></style>