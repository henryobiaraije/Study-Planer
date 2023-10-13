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
      </div>

      <!-- Edit Modal -->
      <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true">
        <div class="modal-dialog">
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
      activeUrl: 'admin.php?page=study-collections',
      trashUrl: 'admin.php?page=study-collections&status=trash',
    }
  },
  setup: (props, ctx) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const status = searchParams.get('status');

    return {
      collections: useCollections(),
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
  },
  created() {
    jQuery('.all-loading').hide();
    this.collections.loadItems();
    console.log('created now');
  },
  methods: {}
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->