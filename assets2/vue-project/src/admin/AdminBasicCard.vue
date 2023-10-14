<template>
  <!--	<editor-fold desc="Header">-->
  <h1 class="wp-heading-inline">{{ pageTitle }}</h1>
  <br/>
  <!--	</editor-fold  desc="Header">--><!--  Body  -->
  <form v-if="showMain" @submit.prevent="basicCard.createOrUpdate()" class="rounded md:p-4 shadow bg-sp-300"
        style="max-width:1000px; margin: auto">
    <label class="bg-white my-2 p-2 rounded shadow flex gap-2 items-center">
      <span class="">Name</span>
      <input v-model="basicCardGroup.name" required type="text">
    </label>
    <div class="sp-wp-editor bg-white my-2 p-2 rounded shadow">
      <span class="editor-title">Question</span>
      <div class="editor-input">
        <input-editor
            v-model="basicCardGroup.whole_question"
            :value="basicCardGroup.whole_question"
        ></input-editor>
      </div>
    </div>
    <div class="sp-wp-editor bg-white my-2 p-2 rounded shadow">
      <span class="editor-title">Answer</span>
      <div class="editor-input">
        <input-editor v-model="basicCardItem.answer" :value="basicCardItem.answer"></input-editor>
      </div>
    </div>
    <div class="bg-white my-2 p-2 rounded shadow">
      <!--      <span class="">Scheduled at (optional)</span>-->
      <!--      <div class="border-1 p-1 px-2 mb-3 mt-0">-->
      <!--        <label>-->
      <!--          <span> </span>-->
      <!--          <input v-model="basicCardGroup.scheduled_at" type="datetime-local" :required="false">-->
      <!--        </label>-->
      <!--      </div>-->
      <div v-if="isEditing" class="flex bg-sp-100 rounded ">
        <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
          Created:
          <time-comp :time="basicCardGroup?.created_at ? basicCardGroup.created_at : ''"></time-comp>
        </div>
        <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
          Updated:
          <time-comp :time="basicCardGroup?.updated_at ? basicCardGroup.updated_at : ''"></time-comp>
        </div>
        <div v-if="null !== basicCardGroup?.deleted_at"
             class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
          Trashed:
          <time-comp :time="basicCardGroup?.deleted_at ? basicCardGroup.deleted_at : ''"></time-comp>
        </div>
      </div>
    </div>
    <label class="sp-wp-checkbox border-1 bg-white my-2 p-2 rounded shadow flex gap-2 items-center">
      <span>Reverse</span>
      <input v-model="basicCardGroup.reverse" type="checkbox" class="">
    </label>
    <div class="bg-white my-2 p-2 rounded shadow">
      <span>Deck </span>
      <vue-mulitiselect
          v-model="basicCardGroup.deck"
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
    <div class="bg-white my-2 p-2 rounded shadow">
      <span>Topic </span>
      <vue-mulitiselect
          v-model="basicCardGroup.topic"
          :options="topics.searchResults.value"
          :multiple="false"
          :loading="topics.ajaxSearch.value.sending"
          :searchable="true"
          :allowEmpty="false"
          :close-on-select="true"
          :taggable="false"
          :createTag="false"
          @search-change="topics.search"
          placeholder="Topic"
          label="name"
          track-by="id"
      ></vue-mulitiselect>
    </div>
    <div class="bg-white my-2 p-2 rounded shadow">
      <span> Collection </span>
      <vue-mulitiselect
          v-model="basicCardGroup.collection"
          :options="collections.searchResults.value"
          :multiple="false"
          :loading="collections.ajaxSearch.value.sending"
          :searchable="true"
          :allowEmpty="false"
          :close-on-select="true"
          :taggable="false"
          :createTag="false"
          @search-change="collections.search"
          placeholder="Collection"
          label="name"
          track-by="id"
      ></vue-mulitiselect>
      <p class="text-xs text-gray-400 py-1">When you select a collection, the card will not be displayed on the frontend
        until the collection is
        published.</p>
    </div>
    <div class="mt-2 mb-2 bg-white my-2 p-2 rounded shadow">
      <span>Tags</span>
      <vue-mulitiselect
          v-model="basicCardGroup.tags"
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
    <div class="my-4 bg-white p-2 rounded shadow">
      <span>Background Image</span>
      <label class=" mb-2 shadow flex gap-2 items-center ">
        <span>Set as Default</span>
        <input v-model="basicCard.setBgAsDefault.value" type="checkbox">
      </label>
      <pick-image
          v-model="basicCardGroup.bg_image_id"
          :default-image="spClientData().localize.default_bg_image"
          :value="basicCardGroup.bg_image_id"></pick-image>
    </div>
    <ajax-action
        :button-text="isEditing ? 'Update' : 'Create'"
        css-classes="button"
        :icon="isEditing ? 'fa-upload' : 'fa-plus'"
        :ajax="basicCard.ajaxCreate.value">
    </ajax-action>
  </form>
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
import useTagSearch from "@/composables/useTagSearch";
import useDecks from "@/composables/useDecks";
import useBasicCard from "@/composables/useBasicCard";
import InputEditor from "@/vue-component/InputEditor.vue";
import {spClientData} from "@/functions";
import PickImage from "@/vue-component/PickImage.vue";
import useCollections from "@/composables/useCollections";
import useTopics from "@/composables/useTopics";

export default defineComponent({
  name: 'AdminBasicCard',
  components: {
    PickImage,
    InputEditor,
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
      // pageTitle: 'All Cards',
      // activeUrl: 'admin.php?page=study-planner-deck-cards',
      // trashUrl: 'admin.php?page=study-planner-deck-cards&status=trash',
      value: '',
      showMain: false,
    }
  },
  setup: (props, ctx) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const status = searchParams.get('status');
    const action = searchParams.get('action');
    const cardGroupId = Number(searchParams.get('card-group'));

    return {
      decks: useDecks(status),
      searchTags: useTagSearch(),
      basicCard: useBasicCard(cardGroupId),
      collections: useCollections(),
      topics: useTopics(),
    }
  },
  computed: {
    pageTitle() {
      let url = new URL(window.location.href);
      let cardGroup = url.searchParams.get("card-group");
      if (null !== cardGroup && cardGroup.length > 0) {
        return 'Edit Card';
      }
      return 'New Card';
    },
    isEditing() {
      let url = new URL(window.location.href);
      let cardGroup = url.searchParams.get("card-group");
      return null !== cardGroup && cardGroup.length > 0;
    },
    deckToEdit() {
      return this.decks.itemToEdit.value;
    },
    basicCardItem() {
      return this.basicCard.item.value;
    },
    basicCardGroup() {
      return this.basicCard.cardGroup.value;
    },
  },
  created() {
    // jQuery('.all-loading').hide();
    this.basicCard.load().then(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-loaded').show();
      this.showMain = true;
    }).catch(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-error').show();
    });
  },
  methods: {
    spClientData,
    setQuestion(value) {
      if ('string' === typeof value) {
        this.basicCardGroup.whole_question = value;
      }
    }
  }
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->