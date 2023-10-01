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
            <!--							<vue-mulitiselect-->
            <!--									v-model="deckNew.tags"-->
            <!--									:options="searchTags.results.value"-->
            <!--									:multiple="true"-->
            <!--									:loading="searchTags.ajax.value.sending"-->
            <!--									:searchable="true"-->
            <!--									:close-on-select="true"-->
            <!--									:taggable="true"-->
            <!--									:createTag="false"-->
            <!--									@tag="searchTags.addTag"-->
            <!--									@search-change="searchTags.search"-->
            <!--									placeholder="Tags"-->
            <!--									label="name"-->
            <!--									track-by="id"-->
            <!--							></vue-mulitiselect >-->
          </div>
          <div class="mt-3">
            <!--							<ajax-action-->
            <!--									button-text="Create"-->
            <!--									css-classes="button"-->
            <!--									icon="fa fa-plus"-->
            <!--									:ajax="decks.ajaxCreate.value" >-->
            <!--							</ajax-action >-->
          </div>
        </form>
      </div>
      <div class="table-area flex-1">
        Table goes here..
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

const pageTitle = 'Topics';
const activeUrl = 'admin.php?page=study-planner-topics';
const trashUrl = 'admin.php?page=study-planner-topics&status=trash';

// <editor-fold desc="Composable">
const decks = useDecks();
const deckGroups = useDeckGroupLists();

// </editor-fold desc="Composable">

// const value = ref('');
// const options = ['Select option', 'options', 'selected', 'multiple', 'label', 'searchable', 'clearOnSelect', 'hideSelected', 'maxHeight', 'allowEmpty', 'showLabels', 'onChange', 'touched']

// <editor-fold desc="Computed">

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
// </editor-fold desc="Computed">

onMounted(() => {
  jQuery('.all-loading').hide();
  jQuery('.all-loaded').show();
  console.log('hiding and showin')
});

</script>

<style scoped lang="scss">

</style>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
