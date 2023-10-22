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
  <ul>
    <li>All the cards in these collections will not be shown on the frontend.</li>
    <li><b>Publish: </b>Publish a collection to make all the cards in collection accessible from the frontend.</li>
    <li><b>Delete: </b>When you delete a collection, all the cards in that collection will also be published and the
      collection will be deleted.
    </li>
  </ul>

  <!--  Body  -->
  <div class="">
    <div class="flex flex-wrap gap-3 px-1 md:px-4">
      <!--    Form   -->
      <div class="form-area flex-1 md:flex-none  md:w-30 ">
        <form @submit.prevent="collections.create()" class="bg-white rounded p-2">
          <label class="tw-simple-input">
            <span class="tw-title">Collection </span>
            <input v-model="collectionNew.name" name="deck" required type="text">
          </label>
          <div class="mt-3">
            <ajax-action
                button-text="Create"
                css-classes="button"
                icon="fa fa-plus"
                :ajax="collections.ajaxCreate.value">
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
            @page-change="collections.onPageChange"
            @sort-change="collections.onSortChange"
            @column-filter="collections.onColumnFilter"
            @per-page-change="collections.onPerPageChange"
            @selected-rows-change="collections.onSelect"
            @search="collections.onSearch"
        >
          <template slot="table-row" #table-row="props">
            <div v-if="props.column.field === 'name'">
              <input @input="collections.onEdit(props.row)"
                     :disabled="props.row.name === 'Uncategorized' || inTrash"
                     v-model="props.row.name"/>
              <div class="row-actions">
									<span class="edit">
									<a @click.prevent="collections.openEditModal(props.row,'#modal-edit')" class="text-blue-500 font-bold"
                     href="#">
									Edit <i class="fa fa-pen-alt"></i></a>  </span>
              </div>
            </div>
            <div v-else-if="props.column.field === 'deck_group'">
              {{ props.row.deck_group ? props.row.deck_group.name : '' }}
            </div>
            <div v-else-if="props.column.field === 'publish'">
              <ajax-action
                  button-text="Publish"
                  css-classes="button !px-2 !py-1"
                  icon="fa fa-save"
                  :show-icon="false"
                  @click="publish(props.row)"
                  :ajax="{
                    ...collections.ajaxPublish.value,
                    sending: itemsBeingPublished.includes(props.row.id),
                }">
              </ajax-action>
            </div>
            <div v-else-if="props.column.field === 'delete'">
              <ajax-action
                  button-text="Delete"
                  css-classes="button !px-2 !py-1"
                  icon="fa fa-save"
                  :show-icon="false"
                  @click="deleteItem(props.row)"
                  :ajax="{
                    ...collections.ajaxDelete.value,
                    sending: itemsBeingDeleted.includes(props.row.id),
                }"
              >
              </ajax-action>
            </div>
            <div v-else-if="props.column.field === 'cards'">
              {{ props.row.card_groups ? props.row.card_groups.length : 0 }}
              {{ props.row.card_groups && props.row.card_groups.length === 1 ? 'Card' : 'Cards' }}
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
                button-text="Delete Selected Permanently "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="collections.batchDelete()"
                :ajax="collections.ajaxDelete.value">
            </ajax-action-not-form>
            <ajax-action-not-form
                v-else
                button-text="Trash Selected "
                css-classes="button button-link-delete"
                icon="fa fa-trash"
                @click="collections.batchTrash()"
                :ajax="collections.ajaxTrash.value">
            </ajax-action-not-form>
          </template>
        </vue-good-table>
      </div>

      <!-- Edit Modal -->
      <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true">
        <div class="modal-dialog">
          <form @submit.prevent="collections.updateEditing" class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalEdit">Edit Collection</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div v-if="null !== collectionToEdit" class="modal-body">
              <label class="tw-simple-input">
                <span class="tw-title">Collection </span>
                <input v-model="collectionToEdit.name" name="deck" required type="text">
              </label>
            </div>
            <div class="modal-footer">
              <div class="mt-3">
                <ajax-action
                    button-text="Update"
                    css-classes="button"
                    icon="fa fa-plus"
                    :ajax="collections.ajaxUpdate.value">
                </ajax-action>
              </div>
            </div>
          </form>
        </div>
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
import useCollections from "@/composables/useCollections";
import type {_Collection} from "@/interfaces/inter-sp";

export default defineComponent({
  name: 'AdminCollections',
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
      pageTitle: 'Collections',
      activeUrl: 'admin.php?page=study-planner-pro-collections',
      trashUrl: 'admin.php?page=study-planner-pro-collections&status=trash',
      itemsBeingPublished: [],
      itemsBeingDeleted: [],
    }
  },
  setup: (props, ctx) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const status = searchParams.get('status');

    return {
      collections: useCollections(status),
      searchTags: useTagSearch(),
    }
  },
  computed: {
    totalActive() {
      // return this.deckGroups.tableData.value.totalRecords;
      return this.collections.totals.value.active;
    },
    totalTrash() {
      return this.collections.totals.value.trashed;
    },
    inTrash() {
      let url = new URL(window.location.href);
      let status = url.searchParams.get("status");
      return status === 'trash';
    },
    collectionNew() {
      return this.collections.newItem.value;
    },
    tableDataValue() {
      return this.collections.tableData.value;
    },
    collectionToEdit() {
      return this.collections.itemToEdit.value;
    }
  },
  created() {
    jQuery('.all-loading').hide();
    this.collections.loadItems();
    console.log('created now');
  },
  methods: {
    publish(item: _Collection) {
      this.itemsBeingPublished.push(item.id);
      this.collections.publish([item])
          ?.finally(() => {
            this.itemsBeingPublished = this.itemsBeingPublished.filter((id) => id !== item.id);
          });
    },
    deleteItem(item: _Collection) {
      this.itemsBeingDeleted.push(item.id);
      this.collections.delete([item])
          ?.finally(() => {
            this.itemsBeingDeleted = this.itemsBeingDeleted.filter((id) => id !== item.id);
          });
    },
  }
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->