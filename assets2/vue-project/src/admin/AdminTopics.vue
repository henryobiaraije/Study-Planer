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
    tableDataValue() {
      return this.topics.tableData.value;
    },
  },
  created() {
    jQuery('.all-loading').hide();
    this.topics.loadItems();
  },
  methods: {}
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->