<template>
  <!--	<editor-fold desc="Header">-->
  <h1 class="wp-heading-inline py-2">{{ pageTitle }}</h1>
  <br/>
  <form v-if="showMain" @submit.prevent="useImageCard._createOrUpdate()"
        class="md:p-4  gap-4 flex flex-wrap"
        style="margin: auto">
    <div class="flex-1 ">
      <!--      <?php /**** Name ***/ ?>-->
      <div class="bg-sp-50 shadow rounded sm:p-2 md:p-4 mb-4">
        <label class="bg-white my-2 p-2 rounded shadow">
          <span class="">Name</span>
          <input v-model="imageCardGroup.name" name="card_name" required type="text">
        </label>
      </div>
      <div class="action-buttons mb-2">
        <button @click="useImageCard._AddImage()" type="button" class="button">Add Image</button>
        <button @click="useImageCard._AddBox()" type="button" class="button">Add Box</button>
        <select @change="useImageCard._refreshPreview()" v-model="imageCardGroup.image_type" required>
          <option value="">Display Type</option>
          <option value="hide_all_ask_one">Hide All - Ask One</option>
          <option value="hide_all_ask_all">Hide All - Ask All</option>
          <option value="hide_one_ask_one">Hide One - Ask One</option>
        </select>
      </div>
      <!--      <?php /**** Image display ***/ ?>-->
      <div class="bg-white shadow rounded sm:p-2 md:p-4 mb-4">
        <div class="image-area">
          <div :id="'main-'+imageCardItem.hash" class="image-area-inner image-card-view ">
							<span :id="'sp-box-'+item.hash"
                    :data-hash="item.hash" v-for="(item,itemIndex) in imageCardItem.boxes"
                    @dblclick="useImageCard._openActionMenu(item)"
                    :key="item.hash"
                    class="sp-boxes">
								<img v-if="item.imageUrl.length > 0" :src="item.imageUrl" alt="">
								<div class="position-relative">
									<span :id="'action-box-' + item.hash " class="position-absolute top-0 right-0"></span>
								</div>
							</span>
          </div>
        </div>
        <div class="image-menu-action action-menu" id="image-menu-action" style="display: none; z-index:2147483647">
          <ul class="m-0 p-0 shadow rounded overflow-hidden bg-white" style="max-width: 180px;font-size: 14px;">
            <!--							<li @click="useImageCard._bringToFront" class="" >Bring to front</li >-->
            <!--							<li @click="useImageCard._sendToBack" class="" >Send to back</li >-->
            <li @click="useImageCard._delete" class="">Delete</li>
          </ul>
        </div>
      </div>
      <!--      <?php /**** Cards formed ***/ ?>-->
      <div class="card-preview rounded-3 p-2 bg-white border-1 border-sp-200">
        <h3 class="font-bold fs-5  my-2">Cards formed ({{ useImageCard.items.value.length }})
          <i @click="useImageCard._refreshPreview()"
             class="fa fa-recycle fs-6 bg-white p-1 rounded-full hover:rotate-180 cursor-pointer"></i></h3>
        <ul>
          <li v-for="(item,itemIndex) in useImageCard.items.value"
              :data-hash="item.hash" style="max-width: 90vw;"
              class="bg-white p-2 rounded-3 overflow-x-auto">
            <ul class="flex ">
              <li><b>Question:</b>
                <div class="image-area" :style="{height: item.question.h+'px' }">
                  <div :id="'main-preview-'+item.question.hash" class="image-area-inner-preview image-card-view ">
											<span v-for="(item2,itemIndex2) in item.question.boxes" :id="'sp-box-preview-'+item2.hash"
                            :class="{'show-box': item2.show, 'asked-box' : item2.asked, 'hide-box' : item2.hide }"
                            :key="item2.hash" class="sp-box-preview">
												<span v-if="item2.imageUrl.length < 2">{{ item.c_number }}</span>
												<img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt="">
											</span>
                  </div>
                </div>
              </li>
              <li><b>Answer:</b>
                <div class="image-area" :style="{height: item.answer.h+'px' }">
                  <div :id="'main-preview-'+item.answer.hash" class="image-area-inner-preview image-card-view ">
											<span v-for="(item2,itemIndex2) in item.answer.boxes" :id="'sp-box-preview-'+item2.hash"
                            :class="{'show-box': item2.show, 'hide-box' : item2.hide }"
                            :key="item2.hash" class="sp-box-preview">
<!--												<span v-if="item2.imageUrl.length < 2" >{{item.c_number}}</span >-->
												<img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt=""
                             :style="{width : item2.w+'px', height : item2.h+'px'}">
											</span>
                  </div>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
    <div class="sm:flex-1 md:flex-initial">
      <div class=" bg-sp-300 shadow rounded sm:p-2 md:p-4" style="max-width: 300px">
        <ajax-action
            :button-text="isEditing ? 'Update' : 'Create'"
            css-classes="button"
            :icon="isEditing ? 'fa fa-upload' : 'fa fa-plus'"
            :ajax="useImageCard.ajaxCreate.value">
        </ajax-action>
        <!--        <?php /**** Scheduled ***/ ?>-->
        <div class="bg-white my-2 p-2 rounded shadow">
          <span class="">Scheduled at (optional)</span>
          <div class="border-1 p-1 px-2 mb-3 mt-0">
            <label>
              <span> </span>
              <input v-model="imageCardGroup.scheduled_at" type="datetime-local">
            </label>
          </div>
          <div v-if="isEditing" class="flex flex-wrap bg-sp-100 rounded ">
            <div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full">
              Created:
              <time-comp :time="imageCardGroup.created_at ? imageCardGroup.created_at : ''"></time-comp>
            </div>
            <div class="rounded bg-white text-black flex-1 m-2 p-1 text-center md:w-full">
              Scheduled:
              <time-comp :time="imageCardGroup.scheduled_at"></time-comp>
            </div>
            <div class="rounded bg-white text-black flex-1 m-2 p-1 text-center md:w-full">
              Updated:
              <time-comp :time="imageCardGroup.updated_at ? imageCardGroup.updated_at : ''"></time-comp>
            </div>
            <div v-if="imageCardGroup.deleted_at "
                 class="rounded bg-white text-black flex-1 m-2 p-1 text-center md:w-full">
              Trashed:
              <time-comp :time="imageCardGroup.deleted_at ? imageCardGroup.deleted_at : ''"></time-comp>
            </div>
          </div>
        </div>
        <!--        <?php /**** Deck ***/ ?>-->
        <div class="bg-white my-2 p-2 rounded shadow">
          <span>Deck </span>
          <vue-mulitiselect
              v-model="imageCardGroup.deck" :options="decks.searchResults.value"
              :multiple="false" :loading="decks.ajaxSearch.value.sending"
              required
              :searchable="true" :allowEmpty="false" :close-on-select="true"
              :taggable="false" :createTag="false" @search-change="decks.search"
              placeholder="Deck" label="name" track-by="id"
          ></vue-mulitiselect>
        </div>
        <!--        <?php /**** Tags ***/ ?>-->
        <div class="mt-2 mb-2 bg-white my-2 p-2 rounded shadow">
          <span>Tags</span>
          <vue-mulitiselect
              v-model="imageCardGroup.tags" :options="searchTags.results.value" :multiple="true"
              :loading="searchTags.ajax.value.sending" :searchable="true" :close-on-select="true"
              :taggable="true" :createTag="false" @tag="searchTags.addTag"
              @search-change="searchTags.search" placeholder="Tags" label="name" track-by="id"
          ></vue-mulitiselect>
        </div>
        <!--        <?php /**** Background Image ***/ ?>-->
        <div class="bg-white my-2 p-2 rounded shadow">
          <span>Background Image</span>
          <label class="block mb-2">
            <span>Set as Default</span>
            <input v-model="useImageCard.setBgAsDefault.value" type="checkbox">
          </label>
          <pick-image
              v-model="imageCardGroup.bg_image_id"
              :default-image="spClientData().localize.default_bg_image"
              value="imageCardGroup.bg_image_id"></pick-image>
        </div>
        <ajax-action
            :button-text="isEditing ? 'Update' : 'Create'"
            css-classes="button"
            :icon="isEditing ? 'fa fa-upload' : 'fa fa-plus'"
            :ajax="useImageCard.ajaxCreate.value">
        </ajax-action>
      </div>
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
import useImageCard from "@/composables/useImageCard";

export default defineComponent({
  name: 'AdminImageCard',
  components: {InputEditorB, InputEditor, PickImage, VueMulitiselect, TimeComp, AjaxAction, HoverNotifications},
  props: {},
  data() {
    return {
      pageTitle: 'Image Card',
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
      decks: useDecks(status),
      searchTags: useTagSearch(),
      useImageCard: useImageCard(cardGroupId),
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
    imageDataValue() {
      return this.decks.tableData.value;
    },
    deckToEdit() {
      return this.decks.itemToEdit.value;
    },
    imageCardItem() {
      return this.useImageCard.imageItem.value;
    },
    imageCardGroup() {
      return this.useImageCard.cardGroup.value;
    },
  },
  created() {
    this.useImageCard._load().then(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-loaded').show();
      this.showMain = true;
      setTimeout(() => {
        this.useImageCard._addEvents();
      }, 1000);
    }).catch(() => {
      jQuery('.all-loading').hide();
      jQuery('.all-error').show();
    });
  },
  methods: {spClientData}
});

</script>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style src="@/css/admin/admin-image-card.scss"></style>