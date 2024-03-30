<template>
  <!--	<editor-fold desc="Header">-->
  <h1 class="wp-heading-inline">{{ pageTitle }}</h1>
  <br/>
  <form v-if="showMain" @submit.prevent="gapCard.createOrUpdate()"
        class=" md:p-4  gap-4 flex flex-wrap"
        style="margin: auto">
    <div class="flex-1 ">
      <div class="bg-sp-300 shadow rounded sm:p-2 md:p-4 mb-4">
        <label class="bg-white my-2 p-2 rounded shadow">
          <span class="">Name</span>
          <input v-model="gapCardGroup.name" name="card_name" required type="text">
        </label>
        <div class="sp-wp-editor bg-white my-2 p-2 rounded shadow">
          <span class="editor-title">Question</span>
          <div class="editor-input">
            <!--							<input-editor-->
            <!--									:value="gapCardGroup.whole_question"-->
            <!--									v-model="gapCardGroup.whole_question" ></input-editor >-->
            <!--            <input-editor-b-->
            <!--                :value="gapCardGroup.whole_question"-->
            <!--                v-model="gapCardGroup.whole_question"></input-editor-b>-->
            <input-editor v-model="gapCardGroup.whole_question" :value="gapCardGroup.whole_question"></input-editor>
          </div>
        </div>
      </div>
      <div class="card-preview mp-ql-editor-content-wrapper  rounded-3 p-2 bg-sp-300 border-1 border-sp-200">
        <div class="ql-editor px-4">
          <h3 class="font-bold fs-5  my-2">Cards formed ({{ gapCard.items.value.length }})
            <i class="fa fa-recycle fs-6 bg-white p-1 rounded-full hover:rotate-180 cursor-pointer"></i></h3>
          <div>
            <div v-for="(item,itemIndex) in gapCard.items.value"
                 :data-hash="item.hash"
                 class="bg-white px-6 py-2 rounded-3 my-2">
              <div class="pl-6 py-2 border border-solid border-gray-100 flex flex-col gap-2">
                <b>Question:</b>
                <div v-html="item.question"></div>
              </div>
              <div class="pl-6 py-2 border border-solid border-gray-100 flex flex-col gap-2">
                <b>Answer:</b>
                <div v-html="item.answer"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="sm:flex-1 md:flex-initial bg-sp-300 shadow rounded sm:p-2 md:p-4" style="max-width: 300px">
      <ajax-action
          :button-text="isEditing ? 'Update' : 'Create'"
          css-classes="button"
          :icon="isEditing ? 'fa fa-upload' : 'fa fa-plus'"
          :ajax="gapCard.ajaxCreate.value">
      </ajax-action>
      <div class="bg-white my-2 p-2 rounded shadow">
        <span class="">Scheduled at (optional)</span>
        <div class="border-1 p-1 px-2 mb-3 mt-0">
          <label>
            <span> </span>
            <input v-model="gapCardGroup.scheduled_at" type="datetime-local" step="any">
          </label>
        </div>
        <div v-if="isEditing" class="flex flex-wrap bg-sp-100 rounded ">
          <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
            Created:
            <time-comp :time="gapCardGroup.created_at ? gapCardGroup.created_at : ''"></time-comp>
          </div>
          <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
            Scheduled:
            <time-comp :time="gapCardGroup.scheduled_at"></time-comp>
          </div>
          <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
            Updated:
            <time-comp :time="gapCardGroup.updated_at ? gapCardGroup.updated_at : ''"></time-comp>
          </div>
          <div v-if="gapCardGroup.deleted_at?.length > 0"
               class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
            Trashed:
            <time-comp :time="gapCardGroup.deleted_at ? gapCardGroup.deleted_at : ''"></time-comp>
          </div>
        </div>
      </div>
      <div class="bg-white my-2 p-2 rounded shadow">
        <span>Deck </span>
        <vue-mulitiselect
            v-model="gapCardGroup.deck" :options="decks.searchResults.value"
            :multiple="false" :loading="decks.ajaxSearch.value.sending"
            :searchable="true" :allowEmpty="false" :close-on-select="true"
            :taggable="false" :createTag="false" @search-change="decks.search"
            placeholder="Deck" label="name" track-by="id"
        ></vue-mulitiselect>
      </div>
      <div class="bg-white my-2 p-2 rounded shadow">
        <span>Topic </span>
        <vue-mulitiselect
            v-model="gapCardGroup.topic"
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
        <span> Collection
          <button type="button" @click="clearCollection" class="mx-4 !text-gray-500 !hover:bg-gray-100" style="opacity: 50%">Clear</button>
        </span>
        <vue-mulitiselect
            v-model="gapCardGroup.collection"
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
        <p class="text-xs text-gray-400 py-1">When you select a collection, the card will not be displayed on the
          frontend
          until the collection is
          published.</p>
      </div>
      <!--      <?php /**** Tags ***/ ?>-->
      <div class="mt-2 mb-2 bg-white my-2 p-2 rounded shadow">
        <span>Tags 2</span>
        <vue-mulitiselect
            v-model="gapCardGroup.tags" :options="searchTags.results.value" :multiple="true"
            :loading="searchTags.ajax.value.sending" :searchable="true" :close-on-select="true"
            :taggable="true" :createTag="false" @tag="searchTags.addTag"
            @search-change="searchTags.search" placeholder="Tags" label="name" track-by="id"
        ></vue-mulitiselect>
      </div>
      <!--      <?php /**** Background Image ***/ ?>-->
      <div class="my-4 bg-white p-2 rounded shadow">
        <span>Background Image</span>
        <label class="block mb-2">
          <span>Set as Default</span>
          <input v-model="gapCard.setBgAsDefault.value" type="checkbox">
        </label>
        <pick-image
            v-model="gapCardGroup.bg_image_id"
            :default-image="spClientData().localize.default_bg_image"
            :value="gapCardGroup.bg_image_id"
        ></pick-image>
      </div>
      <ajax-action
          :button-text="isEditing ? 'Update' : 'Create'"
          css-classes="button"
          :icon="isEditing ? 'fa fa-upload' : 'fa fa-plus'"
          :ajax="gapCard.ajaxCreate.value">
      </ajax-action>
    </div>
  </form>

  <!--	</editor-fold  desc="Header">--><!--  Body  -->
  <br/>
  <hover-notifications></hover-notifications>
</template>
<script lang="ts">
import {defineComponent} from "vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import HoverNotifications from "@/vue-component/HoverNotifications.vue";
import TimeComp from "@/vue-component/TimeComp.vue";
// @ts-ignore
// import {VueGoodTable} from 'vue-good-table-next';
// import the styles
// import 'vue-good-table-next/dist/vue-good-table-next.css'
import useTagSearch from "@/composables/useTagSearch";
import useDecks from "@/composables/useDecks";
import useBasicCard from "@/composables/useBasicCard";
import PickImage from "@/vue-component/PickImage.vue";
import useGapCard from "@/composables/useGapCard";
import VueMulitiselect from "vue-multiselect";
import {spClientData} from "@/functions";
import InputEditor from "@/vue-component/InputEditor.vue";
import InputEditorB from "@/vue-component/InputEditorB.vue";
import useTopics from "@/composables/useTopics";
import useCollections from "@/composables/useCollections";

export default defineComponent({
  name: 'AdminGapCard',
  components: {InputEditorB, InputEditor, PickImage, VueMulitiselect, TimeComp, AjaxAction, HoverNotifications},
  props: {},
  data() {
    return {
      pageTitle: 'Gap Card',
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
      gapCard: useGapCard(cardGroupId),
      topics: useTopics(),
      collections: useCollections(),
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
    tableDataValue() {
      return this.decks.tableData.value;
    },
    deckToEdit() {
      return this.decks.itemToEdit.value;
    },
    gapCardItem() {
      return this.gapCard.items.value;
    },
    gapCardGroup() {
      return this.gapCard.cardGroup.value;
    },
  },
  created() {
    this.gapCard.load().then(() => {
      // @ts-ignore
      jQuery('.all-loading').hide();
      // @ts-ignore
      jQuery('.all-loaded').show();
      this.showMain = true;
    }).catch(() => {
      // @ts-ignore
      jQuery('.all-loading').hide();
      // @ts-ignore
      jQuery('.all-error').show();
    });
    this.triggerLoadMultiSelects();
  },
  methods: {
    spClientData,
    triggerLoadMultiSelects() {
      this.decks.search('');
      this.collections.search('');
      this.topics.search('');
      this.searchTags.search('');
    },
    clearCollection() {
      this.gapCardGroup.collection_id = null;
      this.gapCardGroup.collection = null;
    }
  }
});

</script>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->