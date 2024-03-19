<template>
  <!--	<editor-fold desc="Header">-->
  <h1 class="wp-heading-inline">{{ pageTitle }}</h1>
  <br/>
  <form v-if="showMain" @submit.prevent="tableCard._createOrUpdate()"
        class="md:p-4  gap-4 flex flex-wrap"
        style="margin: auto">
    <div class="flex-1 ">
      <!--      <?php /**** Name ***/ ?>-->
      <div class="bg-sp-50 shadow rounded sm:p-2 md:p-4 mb-4">
        <label class="bg-white my-2 p-2 rounded shadow">
          <span class="">Name</span>
          <input v-model="tableCardGroup.name" name="card_name" required type="text">
        </label>
      </div>
      <div class="action-buttons mb-2">
        <button @click="tableCard._tAddColumn(null)" type="button" class="button">Add Column</button>
        <button @click="tableCard._tAddRow(null)" type="button" class="button">Add Row</button>
      </div>
      <!--      <?php /**** Table display ***/ ?>-->
      <div class="bg-sp-300 shadow rounded sm:p-2 md:p-4 mb-4">
        <table v-if="tableCard.tableItem.value.length > 0" class="table gap-table shadow p-2 bg-sp-100 rounded">
          <thead>
          <tr>
            <th v-for="(item,itemIndex) in tableCard.tableItem.value[0]"
                @dblclick="tableCard._openTableActionModal(itemIndex,0)"
                class="table-cell border-1 border-sp-200">
              <input-editor
                  :allow-empty="true"
                  @input="tableCard._refreshPreview"
                  :value="item"
                  v-model="tableCard.tableItem.value[0][itemIndex]">
              </input-editor>
              <div class="position-relative">
                <span :id="'table-col-row-' + itemIndex  + '-' + 0" class="position-absolute top-0 right-0"></span>
              </div>
            </th>
          </tr>
          </thead>
          <tbody>
          <template v-for="(item,itemIndex) in tableCard.tableItem.value">
            <tr :class="{'bg-gray-100' : (itemIndex / 2 > 0)}"
                v-if="itemIndex !== 0">
              <td @dblclick="tableCard._openTableActionModal(itemIndex2,itemIndex)"
                  v-for="(item2,itemIndex2) in tableCard.tableItem.value[itemIndex]"
                  class="table-cell border-1 border-sp-200">
                <input-editor
                    :allow-empty="true"
                    :value="item2"
                    v-model="tableCard.tableItem.value[itemIndex][itemIndex2]">
                </input-editor>
                <div class="position-relative">
                <span :id="'table-col-row-' + itemIndex2  + '-' + itemIndex"
                      class="position-absolute top-0 right-0"></span>
                </div>
              </td>
            </tr>
          </template>
          </tbody>
        </table>
        <div class="table-action action-menu" id="table-action" style="display: none; z-index:2147483647">
          <ul class="m-0 p-0 shadow rounded overflow-hidden bg-white" style="max-width: 180px;font-size: 14px;">
            <li class="heading p-2 font-medium">Table Action</li>
            <li @click="tableCard._insertRowBefore" :class="actionItemClasses">Insert Row Before</li>
            <li @click="tableCard._insertRowAfter" :class="actionItemClasses">Insert Row After</li>
            <li @click="tableCard._insertColumnBefore" :class="actionItemClasses">Insert Column Before</li>
            <li @click="tableCard._insertColumnAfter" :class="actionItemClasses">Insert Column After</li>
            <li @click="tableCard._deleteColumn" :class="actionItemClasses">Delete Column</li>
            <li @click="tableCard._deleteRow" :class="actionItemClasses">Delete Row</li>
          </ul>
        </div>
      </div>
      <!--      <?php /**** Cards formed ***/ ?>-->
      <div class="card-preview rounded-3 p-2 bg-sp-300 border-1 border-sp-200">
        <h3 class="font-bold fs-5  my-2">Cards formed ({{ tableCard.items.value.length }})
          <i @click="tableCard._refreshPreview"
             class="fa fa-recycle fs-6 bg-white p-1 rounded-full hover:rotate-180 cursor-pointer"></i></h3>
        <ul>
          <li v-for="(item,itemIndex) in tableCard.items.value"
              :data-hash="item.hash"
              class="bg-white p-2 rounded-3">
            <ul>
              <li><b>Question:</b>
                <table v-if="item.question.length > 0" class="table gap-table shadow p-2 bg-sp-100 rounded">
                  <thead>
                  <tr>
                    <th v-for="(item2,itemIndex2) in item.question[0]"
                        class="table-cell border-1 border-sp-200">
                      <div v-html="item2"></div>
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <template v-for="(item2,itemIndex2) in item.question">
                    <tr
                        :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
                        v-if="itemIndex2 !== 0">
                      <td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200">
                        <div v-html="item3"></div>
                      </td>
                    </tr>
                  </template>
                  </tbody>
                </table>
              </li>
              <li><b>Answer:</b>
                <table v-if="item.question.length > 0" class="table gap-table shadow p-2 bg-sp-100 rounded">
                  <thead>
                  <tr>
                    <th v-for="(item2,itemIndex2) in item.answer[0]"
                        class="table-cell border-1 border-sp-200">
                      <div v-html="item2"></div>
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <template v-for="(item2,itemIndex2) in item.answer">
                    <tr
                        :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
                        v-if="itemIndex2 !== 0">
                      <td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200">
                        <div v-html="item3"></div>
                      </td>
                    </tr>
                  </template>
                  </tbody>
                </table>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
    <div class="sm:flex-1 md:flex-initial bg-sp-300 shadow rounded sm:p-2 md:p-4" style="max-width: 300px">
      <ajax-action
          v-if="isEditing"
          :button-text="isEditing ? 'Update' : 'Create'"
          css-classes="button"
          :icon="isEditing ? 'fa fa-upload' : 'fa fa-plus'"
          :ajax="tableCard.ajaxCreate.value">
      </ajax-action>
      <!--      <?php /**** Scheduled ***/ ?>-->
      <div class="bg-white my-2 p-2 rounded shadow">
        <span class="">Scheduled at (optional)</span>
        <div class="border-1 p-1 px-2 mb-3 mt-0">
          <label>
            <span> </span>
            <input v-model="tableCardGroup.scheduled_at" type="datetime-local" step="any">
          </label>
        </div>
        <div v-if="isEditing" class="flex flex-wrap bg-sp-100 rounded ">
          <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
            Created:
            <time-comp :time="tableCardGroup.created_at ? tableCardGroup.created_at : ''"></time-comp>
          </div>
          <div class="rounded bg-white text-black flex-1 m-2 p-1 text-center md:w-full">
            Scheduled:
            <time-comp :time="tableCardGroup.scheduled_at ? tableCardGroup.scheduled_at : ''"></time-comp>
          </div>
          <div class="rounded bg-white text-black flex-1 m-2 p-1 text-center md:w-full">
            Updated:
            <time-comp :time="tableCardGroup.updated_at ? tableCardGroup.updated_at : ''"></time-comp>
          </div>
          <div v-if="tableCardGroup.deleted_at "
               class="rounded bg-white text-black flex-1 m-2 p-1 text-center md:w-full">
            Trashed:
            <time-comp :time="tableCardGroup.deleted_at ? tableCardGroup.deleted_at : ''"></time-comp>
          </div>
        </div>
      </div>
      <!--      <?php /**** Deck ***/ ?>-->
      <div class="bg-white my-2 p-2 rounded shadow">
        <span>Deck </span>
        <vue-mulitiselect
            v-model="tableCardGroup.deck" :options="decks.searchResults.value"
            :multiple="false" :loading="decks.ajaxSearch.value.sending"
            required
            :searchable="true" :allowEmpty="false" :close-on-select="true"
            :taggable="false" :createTag="false" @search-change="decks.search"
            placeholder="Deck" label="name" track-by="id"
        ></vue-mulitiselect>
      </div>
      <div class="bg-white my-2 p-2 rounded shadow">
        <span>Topic </span>
        <vue-mulitiselect
            v-model="tableCardGroup.topic"
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
            v-model="tableCardGroup.collection"
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
        <span>Tags</span>
        <vue-mulitiselect
            v-model="tableCardGroup.tags" :options="searchTags.results.value" :multiple="true"
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
          <input v-model="tableCard.setBgAsDefault.value" type="checkbox">
        </label>
        <pick-image
            v-model="tableCardGroup.bg_image_id"
            :default-image="spClientData().localize.default_bg_image"
            :value="tableCardGroup.bg_image_id"></pick-image>
      </div>
      <ajax-action
          :button-text="isEditing ?  'Update' : 'Create' "
          css-classes="button"
          :icon="isEditing ? 'fa fa-upload' : 'fa fa-plus'"
          :ajax="tableCard.ajaxCreate.value">
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
import useTableCard from "@/composables/useTableCard";
import useTopics from "@/composables/useTopics";
import useCollections from "@/composables/useCollections";

export default defineComponent({
  name: 'AdminTableCard',
  components: {InputEditorB, InputEditor, PickImage, VueMulitiselect, TimeComp, AjaxAction, HoverNotifications},
  props: {},
  data() {
    return {
      pageTitle: 'Table Card',
      showMain: false,
      // actionItemClasses: 'p-2 cursor-pointer !hover:bg-gray-100',
      actionItemClasses: '',
    }
  },
  setup: (props, ctx) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const status = searchParams.get('status');
    const action = searchParams.get('action');
    const cardGroupId = Number(searchParams.get('card-group'));

    // console.log('in setup', {url, searchParams, status, cardGroupId});
    return {
      tableCard: useTableCard(cardGroupId),
      decks: useDecks(status),
      searchTags: useTagSearch(),
      collections: useCollections(),
      topics: useTopics(),
    };
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
    tableCardItem() {
      return this.tableCard.items.value;
    },
    tableCardGroup() {
      return this.tableCard.cardGroup.value;
    },
  },
  created() {
    console.log('created ', this.tableCard);
    this.tableCard._load().then(() => {
      // @ts-ignore
      jQuery('.all-loading').hide();
      // @ts-ignore
      jQuery('.all-loaded').show();
      this.showMain = true;
    }).catch((e) => {
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
    }
  }
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<!--<style src="@/css/admin/admin-table-card.scss"></style>-->
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->