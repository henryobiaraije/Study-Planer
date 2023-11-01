<template>
  <div class="one-accordion-item">
    <form @submit.prevent="userDash.startStudy" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Study ({{ studyToEdit.deck.name }})</h5>
        <button id="hide-modal-new" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="sp-study-input shadow p-2 rounded fs-5 mb-4 shadow p-2 rounded">
          <div class="my-1">Include Tags |
            <label class="cursor-pointer pl-4 bg-gray-200  hover:bg-gray-300 py-1 px-2 rounded">
              <span class="pr-2">Include All :</span>
              <input v-model="studyToEdit.all_tags"
                     class="transform scale-150 mx-1" type="checkbox">
            </label>
          </div>
          <vue-mulitiselect
              v-if="!studyToEdit.all_tags"
              v-model="studyToEdit.tags"
              :options="searchTags.results.value"
              :multiple="true"
              :loading="searchTags.ajax.value.sending"
              :searchable="true"
              :close-on-select="true"
              :taggable="true"
              :createTag="false"
              @tag="searchTags.addTag"
              @search-change="searchTags.search"
              placeholder="Search Tags"
              label="name"
              track-by="id"
          ></vue-mulitiselect>
        </div>
        <div v-if="!studyToEdit.all_tags" class="sp-study-input shadow p-2 rounded fs-5 mb-4 shadow p-2 rounded">
          <div class="my-1">Exclude Tags</div>
          <vue-mulitiselect
              v-model="studyToEdit.tags_excluded"
              :options="searchTags.results.value"
              :multiple="true"
              :loading="searchTags.ajax.value.sending"
              :searchable="true"
              :close-on-select="true"
              :taggable="true"
              :createTag="false"
              @tag="searchTags.addTag"
              @search-change="searchTags.search"
              placeholder="Search Tags"
              label="name"
              track-by="id"
          ></vue-mulitiselect>
        </div>
        <div class="sp-study-input shadow p-2 rounded fs-5 mb-4 sp-study-input">
          <div class="my-1">Number of cards to revise |
            <label class="cursor-pointer pl-4 bg-gray-200   hover:bg-gray-300 py-1 px-2 rounded">
              <span class="pr-2"> All :</span>
              <input v-model="studyToEdit.revise_all"
                     class="transform scale-150 mx-1" type="checkbox">
            </label>
            <label v-if="!studyToEdit.revise_all" class="block my-2">
              <input v-model.number="studyToEdit.no_to_revise"
                     class="w-full bg-white rounded"
                     placeholder="Enter number here" type="text" pattern="[0-9]+">
            </label>
          </div>
        </div>
        <div class="sp-study-input shadow p-2 rounded fs-5 mb-4 sp-study-input">
          <div class="my-1">Number of new cards |
            <label class="cursor-pointer pl-4 bg-gray-200   hover:bg-gray-300 py-1 px-2 rounded">
              <span class="pr-2"> All :</span>
              <input v-model="studyToEdit.study_all_new"
                     class="transform scale-150 mx-1" type="checkbox">
            </label>
            <label v-if="!studyToEdit.study_all_new" class="block my-2">
              <input v-model.number="studyToEdit.no_of_new"
                     class="w-full bg-white rounded"
                     placeholder="Enter number here" type="text" pattern="[0-9]+">
            </label>
          </div>
        </div>
        <div class="sp-study-input shadow p-2 rounded fs-5 mb-4 sp-study-input">
          <div class="my-1">Number of cards on hold |
            <label class="cursor-pointer pl-4 bg-gray-200   hover:bg-gray-300 py-1 px-2 rounded">
              <span class="pr-2"> All :</span>
              <input v-model="studyToEdit.study_all_on_hold"
                     class="transform scale-150 mx-1" type="checkbox">
            </label>
            <label v-if="!studyToEdit.study_all_on_hold" class="block my-2">
              <input v-model="studyToEdit.no_on_hold"
                     class="w-full bg-white rounded"
                     placeholder="Enter number here" type="text" pattern="^[0-9]+$">
            </label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <ajax-action
            button-text="Study"
            css-classes="sp-action-button"
            icon="fa fa-save"
            :ajax="userDash.ajaxSaveStudy.value">
        </ajax-action>
      </div>
    </form>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";
import useAllCards from "@/composables/useAllCards";
import type {_CardGroup, _Deck, _DeckGroup, _Study, _Tag, _Topic} from "@/interfaces/inter-sp";
import QuestionModal from "@/vue-component/QuestionModal.vue";
import {_Card} from "@/interfaces/inter-sp";

export default defineComponent({
  name: 'StudySettingsModal',
  components: {QuestionModal, AjaxAction, CardSelector},
  props: {
    study: {
      type: Object as () => null | _Study,
      required: true,
    },
    deck: {
      type: Object as () => null | _Deck,
      required: true,
    },
    topic: {
      type: Object as () => null | _Topic,
      required: true,
    }
  },
  data() {
    return {
      studyToEdit: this.study || {
        deck: this.deck,
        topic: this.topic,
        tags: Array<_Tag>,
        tags_excluded: Array<_Tag>,
        all_tags: true,
        no_to_revise: 0,
        no_of_new: 0,
        no_on_hold: 0,
        revise_all: true,
        study_all_new: true,
        study_all_on_hold: true,
      }
    }
  },
  setup: (props, ctx) => {
    return {}
  },
  computed: {},
  created() {
  },
  methods: {},
  watch: {},
  beforeUnmount() {
  }
});

</script>
