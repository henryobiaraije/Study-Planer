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
        <form @submit.prevent="topics.create()" class="bg-white rounded p-2">
          <label class="tw-simple-input">
            <span class="tw-title">Topic</span>
            <input v-model="topicNew.name" name="deck" required type="text"/>
          </label>
          <div>
            <span>Deck </span>
            <vue-mulitiselect
                v-model="topicNew.deck"
                :options="decks.searchResults.value"
                :multiple="false"
                :loading="decks.ajaxSearch.value.sending"
                :searchable="true"
                :allowEmpty="false"
                :close-on-select="true"
                :taggable="false"
                :createTag="false"
                @search-change="decks.search"
                placeholder="Deck"
                label="name"
                track-by="id"
            ></vue-mulitiselect>
          </div>
          <div class="mt-2">
            <span>Tags</span>
            <vue-mulitiselect
                v-model="topicNew.tags"
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
          <div class="mt-3">
            <ajax-action
                button-text="Create"
                css-classes="button"
                icon="fa fa-plus"
                :ajax="topics.ajaxCreate.value">
            </ajax-action>
          </div>
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
            @page-change="topics.onPageChange"
            @sort-change="topics.onSortChange"
            @column-filter="topics.onColumnFilter"
            @per-page-change="topics.onPerPageChange"
            @selected-rows-change="topics.onSelect"
            @search="topics.onSearch"
        >
          <template slot="table-row" #table-row="props">
            <div v-if="props.column.field === 'name'">
              <input @input="topics.onEdit(props.row)"
                     :disabled="props.row.name === 'Uncategorized' || inTrash"
                     v-model="props.row.name"/>
              <div v-if="!inTrash" class="row-actions">
									<span class="edit">
									<a @click.prevent="topics.openEditModal(props.row,'#modal-edit')" class="text-blue-500 font-bold"
                     href="#">
									Edit <i class="fa fa-pen-alt"></i></a>  </span>
              </div>
            </div>
            <div v-else-if="props.column.field === 'deck'">
              {{ props.row.deck ? props.row.deck.name : '' }}
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
          <div slot="selected-row-actions">
            <ajax-action-not-form
                v-if="inTrash"
                button-text="Delete Selected Permanently "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="decks.batchDelete()"
                :ajax="decks.ajaxDelete.value">
            </ajax-action-not-form>
            <ajax-action-not-form
                v-else
                button-text="Trash Selected "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="decks.batchTrash()"
                :ajax="decks.ajaxTrash.value">
            </ajax-action-not-form>
          </div>
        </vue-good-table>
      </div>

    </div>

    <!--    <?php /** Edit Modal */ ?>-->
    <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true">
      <div class="modal-dialog">
        <form @submit.prevent="topics.updateEditing" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalEdit">Edit Topic</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div v-if="null !== topicToEdit" class="modal-body">
            <label class="tw-simple-input">
              <span class="tw-title">Topic</span>
              <input v-model="topicToEdit.name" name="topic" required type="text"/>
            </label>
            <div>
              <span>Deck </span>
              <vue-mulitiselect
                  v-model="topicToEdit.deck"
                  :options="decks.searchResults.value"
                  :multiple="false"
                  :loading="decks.ajaxSearch.value.sending"
                  :searchable="true"
                  :allowEmpty="false"
                  :close-on-select="true"
                  :taggable="false"
                  :createTag="false"
                  @search-change="decks.search"
                  placeholder="Deck"
                  label="name"
                  track-by="id"
              ></vue-mulitiselect>
            </div>
            <div>
              <span>Tags</span>
              <vue-mulitiselect
                  v-model="topicToEdit.tags"
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
          </div>
          <div class="modal-footer">
            <ajax-action
                button-text="Update"
                css-classes="button"
                icon="fa fa-save"
                :ajax="topics.ajaxUpdate.value">
            </ajax-action>
          </div>
        </form>
      </div>
    </div>

  </div>
  <br/>
  <hover-notifications></hover-notifications>
</template>
<script lang="ts">
import useDecks from "@/composables/useDecks";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import useTagSearch from "@/composables/useTagSearch";
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
import useTopics from "@/composables/useTopics";

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
      pageTitle: 'Topics',
      activeUrl: 'admin.php?page=study-planner-topics',
      trashUrl: 'admin.php?page=study-planner-topics&status=trash',
      value: null,
      options: ['list', 'of', 'options'],
      selected: null,
    }
  },
  setup: (props, ctx) => {
    const decks = useDecks();
    const deckGroups = useDeckGroupLists();
    const tagSearch = useTagSearch();
    return {
      topics: useTopics(),
      decks,
      tagSearch,
      searchTags: useTagSearch(),
    }
  },
  computed: {
    totalActive() {
      return this.topics.tableData.value.totalRecords;
    },
    totalTrash() {
      return this.topics.tableData.value.totalTrashed;
    },
    inTrash() {
      let url = new URL(window.location.href);
      let status = url.searchParams.get("status");
      return status === 'trash';
    },
    topicNew() {
      return this.topics.newItem.value;
    },
    topicToEdit() {
      return this.topics.itemToEdit.value;
    },
    tableDataValue() {
      return this.topics.tableData.value;
    },
  },
  created() {
    jQuery('.all-loading').hide();
    this.topics.load();
  },
  methods: {}
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->