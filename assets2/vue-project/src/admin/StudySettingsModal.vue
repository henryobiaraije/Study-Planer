<template>
  <form @submit.prevent="saveStudy" class="p-4">
    <div class=" p-4">
      <div class="sp-study-input shadow p-2 rounded fs-5 mb-4 ">
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
      <div v-if="!studyToEdit.all_tags" class="sp-study-input fs-5 mb-4 shadow p-2 rounded">
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
    <div class="">
      <ajax-action
          button-text="Study"
          css-classes="sp-action-button"
          icon="fa fa-save"
          :ajax="userDash.ajaxSaveStudy.value">
      </ajax-action>
    </div>
  </form>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import type {_CardGroup, _Deck, _DeckGroup, _Study, _Tag, _Topic} from "@/interfaces/inter-sp";
import QuestionModal from "@/vue-component/QuestionModal.vue";
import useUserDashboard from "@/composables/useUserDashboard";
import useTagSearch from "@/composables/useTagSearch";
import VueMulitiselect from "vue-multiselect";
import {toast} from "vue3-toastify";
import useUserCards from "@/composables/useUserCards";

export default defineComponent({
  name: 'StudySettingsModal',
  components: {VueMulitiselect, QuestionModal, AjaxAction, CardSelector},
  props: {
    study: {
      type: Object as () => _Study,
      required: true,
    },
    userCards: {
      type: Object as () => ReturnType<typeof useUserCards>,
      required: true,
    },
  },
  data() {
    return {
      studyToEdit: this.study,
    }
  },
  setup: (props, ctx) => {
    return {
      userDash: useUserDashboard(),
      searchTags: useTagSearch(),
    };
  },
  computed: {},
  created() {
    this.searchTags.search('');
  },
  methods: {
    customStringify(obj) {
      const seen = new WeakSet();

      return JSON.stringify(obj, (key, value) => {
        if (typeof value === 'object' && value !== null) {
          if (seen.has(value)) {
            // Handle circular reference here (e.g., return a placeholder)
            return '[Circular Reference]';
          }
          seen.add(value);
        }
        return value;
      });
    },
    saveStudy() {
      let theStudy: _Study = JSON.parse(this.customStringify(this.studyToEdit)) as _Study;
      this.userDash.xhrCreateOrUpdateStudy(theStudy)
          .then(() => {
            toast.success('Study settings Saved.')
            this.userCards.loadUserCards();
          })
    }
  },
  watch: {},
  beforeUnmount() {
  }
});

</script>
