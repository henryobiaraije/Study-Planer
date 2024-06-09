<template>
  <div class="one-accordion-item">
    <div class="accordion-header" :class="[top && !isToggled ? 'pb-2':'']">
      <!-- Header -->
      <div class="sp-deck-group-header border-b border-gray-100">
        <div class="sp-header-title lg:flex">
          <div
              class="header-title-icon flex flex-1 justify-between items-center gap-2 cursor-pointer"
              :class="[cssLeftRight.left]"
          >
            <div @click="toggle()" class="left flex-1 flex gap-2">
              <div v-if="'card_group' !== theItem.childrenType"
                   class="sp-icon flex-initial flex items-center px-2 text-gray-400">
                <v-icon v-if="!isToggled" left>
                  mdi-chevron-right
                </v-icon>
                <v-icon v-if="isToggled" left>
                  mdi-chevron-up
                </v-icon>
              </div>
              <div class="sp-name flex-1 font-medium text-black px-2 py-2 text-base">
                <span @click.prevent="viewCard">{{ theItem.name }}</span>
              </div>
            </div>
            <div class="right flex gap-2 items-center">
              <div v-if="showSettings" class="settings-and-switch flex gap-2">
                <div v-if="null !== currentItemStudy" class="flex items-center">
                  <!--                  <v-switch color="primary" :input-value="studyIsActive" @change="studyChanged"></v-switch>-->
                  <SwitchComp is-round v-model="currentItemStudy.active" @change="studyChanged"/>
                  <!--                  <SwitchComp is-round v-model="studyIsActive" @change="studyChanged"></SwitchComp>-->
                  <!--                  <v-switch color="primary" :v-model="studyIsActive" ></v-switch>-->
                </div>
                <div v-if="studyActive" @click="showStudySettings"
                     class="flex flex-initial items-center hover:opacity-50 cursor-pointer">
                  <v-icon left class="cursor-pointer">
                    mdi-cog-outline
                  </v-icon>
                  <img :src="settingsImageUrl"/>
                </div>
              </div>
              <div class="header-counts flex-initial px-2 text-sm">
                {{ theItem.childrenLength }} {{ theItem.childrenTypeName }}{{ theItem.plural }}
              </div>
            </div>
          </div>
          <div class="sp-deck-count lg:flex flex-1 items-center justify-space-around py-1"
               :class="[cssLeftRight.right]"
          >
            <div v-if="!inMobile" class="sp-deck-count flex-1 flex items-center justify-space-around py-1"
                 :class="[cssLeftRight.right]"
            >
              <template v-for="(stat,statKey) in stats">
                <span class="on-hold bg-white  flex gap-2 justify-center "
                      :class="[stat.count > 0 ? cssLeftRight.right + 'font-semibold' : ' font-normal']">
                  <span class="text-sm px-2 py-1">{{ stat.title }}:</span>
                  <span
                      class="text-sm px-2 rounded-full flex items-center "
                      :class="[stat.count > 0 ? cssLeftRight.left + 'font-semibold' : ' font-normal']"
                  >{{ stat.count }}</span>
                </span>
              </template>
            </div>
            <div v-if="inMobile" class="mobile-stats flex justify-between px-2">
              <div
                  v-for="(stat,statKey) in stats"
                  class="flex flex-row gap-1 items-center"
              >
                <span class="text-sm text-gray-500">{{ stat.title }}: </span>
                <span class="text-base font-semibold text-black">{{ stat.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Body -->
    <div v-if="isToggled" class="accordion-body" :class="[top && isToggled ? 'pb-2' : '']">
      <template v-if="'topic' !== theItem.itemType">
        <template v-for="(child,childIndex) in theItem.children" :key="'topic-'+child.id">
          <AccordionItem :user-cards="userCards" :current-item="child">
          </AccordionItem>
        </template>
      </template>
      <!--      <slot name="body"></slot>-->
    </div>

    <!-- Question Modal -->
    <v-dialog
        v-model="viewDialog"
        width="auto"
    >
      <v-card>
        <v-card-actions>
          <div class="flex flex-row justify-between items-center w-full">
            <span class="flex-1 text-xl !font-bold">Study Cards</span>
            <span class="flex-initial"><v-btn color="primary" block @click="viewDialog = false">Close</v-btn></span>
          </div>
        </v-card-actions>
        <template v-if="null !== studyToEdit">
          <QuestionModal
              title="Study Cards"
              :cards="cardsToView"
              :show-only-answers="false"
              purpose="study"
              :study="studyToEdit"
              :user-cards="userCards"/>
        </template>
        <template v-else>
          <QuestionModal
              title="Study Cards"
              :cards="cardsToView"
              :show-only-answers="false"
              purpose="study"
              :user-cards="userCards"/>
        </template>
      </v-card>
    </v-dialog>

    <!-- Study Modal -->
    <v-dialog
        v-model="viewDialogEditStudy"
        width="auto"
    >
      <v-card>
        <v-card-actions>
          <div class="flex flex-row justify-between items-center w-full">
            <span class="flex-1 text-xl !font-bold">Study Settings</span>
            <span class="flex-initial"><v-btn color="primary"
                                              @click="viewDialogEditStudy = false">Close</v-btn></span>
          </div>
        </v-card-actions>
        <template v-if="studyToEdit">
          <StudySettingsModal :user-cards="userCards" :study="studyToEdit"/>
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";
import useAllCards from "@/composables/useAllCards";
import type {_CardGroup, _Deck, _DeckGroup, _Study, _Topic} from "@/interfaces/inter-sp";
import QuestionModal from "@/vue-component/QuestionModal.vue";
import {_Card, _Tag} from "@/interfaces/inter-sp";
import StudySettingsModal from "@/admin/StudySettingsModal.vue";
import useUserDashboard from "@/composables/useUserDashboard";
import SwitchComp from "@/components/SwitchComp.vue";
import useToggle from "@/composables/useToggle";
import {Store} from "@/static/store";

export default defineComponent({
  name: 'AccordionItem',
  components: {SwitchComp, StudySettingsModal, QuestionModal, AjaxAction, CardSelector},
  props: {
    currentItem: {
      type: Object as () => _DeckGroup | _Deck | _Topic | _CardGroup,
      required: true,
    },
    top: {
      type: Boolean,
      default: false,
    },
    userCards: {
      type: Object as () => ReturnType<typeof useUserCards>,
      required: true,
    },
  },
  data() {
    return {
      item: this.currentItem as _DeckGroup | _Deck | _Topic | _CardGroup,
      switchMe: false,
      viewDialog: false,
      cardsToView: [] as _Card[],
      windowWidth: window.innerWidth,
      viewDialogEditStudy: false,
      studyToEdit: null as null | _Study,
      studyIsActive: true,
      isLoadingCards: false
    }
  },
  setup: (props, ctx) => {
    return {
      allCards: useAllCards(),
      userDash: useUserDashboard(),
      uToggle: useToggle(),
    }
  },
  computed: {
    settingsImageUrl() {

      const localize = Store.localize;
      const image = localize.icon_settings_image;
      console.log("settingsImageUrl", {image});
      return image;
    },
    isToggled() {
      return this.uToggle.isToggled(this.theItem.itemType + '-' + this.theItem.id);
    },
    /**
     * Whether in deck or topic.
     * @return {boolean}
     */
    showSettings(): boolean {
      // In deck or topics.
      return ['deck', 'topic'].includes(this.theItem.itemType);
    },
    inMobile(): boolean {
      // in mobile reactive.
      return this.windowWidth < 768;
    },
    cardsCount(): number {
      let count = 0;
      const item = this.item;
      const theItem = this.theItem;
      if ('deck_group' === theItem.itemType) {
        count = (item as _DeckGroup).decks.reduce((acc, deck) => {
          return acc + deck.topics.reduce((acc, topic) => {
            return acc + topic.card_groups.reduce((acc, cardGroup) => {
              return acc + cardGroup.cards.length;
            }, 0);
          }, 0);
        }, 0);
      } else if ('deck' === theItem.itemType) {
        count = (item as _Deck).topics.reduce((acc, topic) => {
          return acc + topic.card_groups.reduce((acc, cardGroup) => {
            return acc + cardGroup.cards.length;
          }, 0);
        }, 0);
      } else if ('topic' === theItem.itemType) {
        count = (item as _Topic).card_groups.reduce((acc, cardGroup) => {
          return acc + cardGroup.cards.length;
        }, 0);
      }

      return count;
    },
    cardsCountByType__(): { newCards: number, revision: number, onHold: number } {
      const counts = {
        newCards: 0,
        revision: 0,
        onHold: 0,
      };

      const item = this.item;
      const theItem = this.theItem;
      const userCards = this.userCards;
      const newCardIds: number[] = userCards.newCardIds.value;
      const revisionCardIds: number[] = userCards.revisionCardIds.value;
      const holdCardIds: number[] = userCards.onHoldCardIds.value;

      if ('deck_group' === theItem.itemType) {
        counts.newCards = (item as _DeckGroup).decks.reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countNewCards(deck.cards ?? []);
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countNewCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.revision = (item as _DeckGroup).decks.reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countRevisionCards(deck.cards ?? []);
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countRevisionCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.onHold = (item as _DeckGroup).decks.reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countOnHoldCards(deck.cards ?? []);
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countOnHoldCards(topic.cards ?? []);
          }, 0);
        }, 0);
      } else if ('deck' === theItem.itemType) {
        counts.newCards = [(item as _Deck)].reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countNewCards(deck.cards ?? []);
            return deckCardsCount + acc;
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countNewCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.revision = [(item as _Deck)].reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countRevisionCards(deck.cards ?? []);
            return deckCardsCount + acc;
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countRevisionCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.onHold = [(item as _Deck)].reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countOnHoldCards(deck.cards ?? []);
            return deckCardsCount + acc;
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countOnHoldCards(topic.cards ?? []);
          }, 0);
        }, 0);
      } else if ('topic' === theItem.itemType) {
        counts.newCards = [(item as _Topic)].reduce((acc, topic: _Topic) => {
          let topicHasActiveStudy = topic.studies.length > 0 && topic.studies[0].active;
          let topicCardsCount: number = 0;
          if (topicHasActiveStudy) { //
            topicCardsCount = this.countNewCards(topic.cards ?? []);
            return topicCardsCount + acc;
          }
          return topicCardsCount + acc;
        }, 0);
        counts.revision = [(item as _Topic)].reduce((acc, topic: _Topic) => {
          let topicHasActiveStudy = topic.studies.length > 0 && topic.studies[0].active;
          let topicCardsCount: number = 0;
          if (topicHasActiveStudy) { //
            topicCardsCount = this.countRevisionCards(topic.cards ?? []);
            return topicCardsCount + acc;
          }
          return topicCardsCount + acc;
        }, 0);
        counts.onHold = [(item as _Topic)].reduce((acc, topic: _Topic) => {
          let topicHasActiveStudy = topic.studies.length > 0 && topic.studies[0].active;
          let topicCardsCount: number = 0;
          if (topicHasActiveStudy) { //
            topicCardsCount = this.countOnHoldCards(topic.cards ?? []);
            return topicCardsCount + acc;
          }
          return topicCardsCount + acc;
        }, 0);
      }

      return counts;
    },
    cardsCountByType_(): { newCards: number, revision: number, onHold: number } {
      const counts = {
        newCards: 0,
        revision: 0,
        onHold: 0,
      };

      const item = this.item;
      const theItem = this.theItem;
      const userCards = this.userCards;
      const newCardIds: number[] = userCards.newCardIds.value;
      const revisionCardIds: number[] = userCards.revisionCardIds.value;
      const holdCardIds: number[] = userCards.onHoldCardIds.value;

      if ('deck_group' === theItem.itemType) {
        counts.newCards = (item as _DeckGroup).decks.reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countNewCards(deck.cards ?? []);
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countNewCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.revision = (item as _DeckGroup).decks.reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countRevisionCards(deck.cards ?? []);
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countRevisionCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.onHold = (item as _DeckGroup).decks.reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countOnHoldCards(deck.cards ?? []);
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countOnHoldCards(topic.cards ?? []);
          }, 0);
        }, 0);
      } else if ('deck' === theItem.itemType) {
        counts.newCards = [(item as _Deck)].reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countNewCards(deck.cards ?? []);
            return deckCardsCount + acc;
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countNewCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.revision = [(item as _Deck)].reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countRevisionCards(deck.cards ?? []);
            return deckCardsCount + acc;
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countRevisionCards(topic.cards ?? []);
          }, 0);
        }, 0);
        counts.onHold = [(item as _Deck)].reduce((acc, deck: _Deck) => {
          let deckHasActiveStudy = deck.studies.length > 0 && deck.studies[0].active;
          let deckCardsCount: number = 0;
          if (deckHasActiveStudy) { //
            deckCardsCount = this.countOnHoldCards(deck.cards ?? []);
            return deckCardsCount + acc;
          }
          return deckCardsCount + acc + deck.topics.reduce((acc, topic) => {
            return acc + this.countOnHoldCards(topic.cards ?? []);
          }, 0);
        }, 0);
      } else if ('topic' === theItem.itemType) {
        counts.newCards = [(item as _Topic)].reduce((acc, topic: _Topic) => {
          let topicHasActiveStudy = topic.studies.length > 0 && topic.studies[0].active;
          let topicCardsCount: number = 0;
          if (topicHasActiveStudy) { //
            topicCardsCount = this.countNewCards(topic.cards ?? []);
            return topicCardsCount + acc;
          }
          return topicCardsCount + acc;
        }, 0);
        counts.revision = [(item as _Topic)].reduce((acc, topic: _Topic) => {
          let topicHasActiveStudy = topic.studies.length > 0 && topic.studies[0].active;
          let topicCardsCount: number = 0;
          if (topicHasActiveStudy) { //
            topicCardsCount = this.countRevisionCards(topic.cards ?? []);
            return topicCardsCount + acc;
          }
          return topicCardsCount + acc;
        }, 0);
        counts.onHold = [(item as _Topic)].reduce((acc, topic: _Topic) => {
          let topicHasActiveStudy = topic.studies.length > 0 && topic.studies[0].active;
          let topicCardsCount: number = 0;
          if (topicHasActiveStudy) { //
            topicCardsCount = this.countOnHoldCards(topic.cards ?? []);
            return topicCardsCount + acc;
          }
          return topicCardsCount + acc;
        }, 0);
      }

      return counts;
    },
    theItem() {
      const item = this.item;
      const id = item.id;

      const parentMap = {
        'decks': 'deck_group',
        'topics': 'deck',
        'card_groups': 'topic',
      };
      let itemType = parentMap[Object.keys(item).find(key => key in parentMap)] || 'card_group';

      const childrenMap = {
        'decks': 'deck',
        'topics': 'topic',
        'card_groups': 'card_group',
      };
      let childrenType = childrenMap[Object.keys(item).find(key => key in childrenMap)] || 'card_group';

      const childrenMapNames = {
        'decks': 'subject',
        'topics': 'topic',
        'card_groups': 'card',
      };

      let childrenTypeName = childrenMapNames[Object.keys(item).find(key => key in childrenMapNames)] || 'card_group';
      // console.log({childrenType: childrenType, map: childrenMap, item});

      let childrenLength = 0;

      // console.log({itemType, childrenType, item: this.item});

      if ('card_group' === childrenType) {
        // console.log('card_groups..', {item});
        childrenLength = item[childrenType + 's'].reduce((acc, cardGroup) => {
          // console.log({acc, cardGroup});
          return acc + cardGroup.cards.length;
        }, 0);
      } else {
        childrenLength = item[childrenType + 's']?.length || 0;
      }

      // console.log({itemType, childrenType, item: this.item});
      // debugger;
      return {
        id,
        name: item.name,
        itemType: itemType,
        childrenType: childrenType,
        children: item[childrenType + 's'],
        plural: (item[childrenType + 's'].length > 1) ? 's' : '',
        childrenTypeName,
        childrenLength,
        item
      };
    },
    stats() {
      return {
        onHold: {
          title: 'On hold',
          // count: this.cardsCountByType.onHold,
          count: this.item?.count_on_hold,
        },
        revision: {
          title: 'Revision',
          // count: this.cardsCountByType.revision,
          count: this.item?.count_revision
        },
        newCards: {
          title: 'New Cards',
          // count: this.cardsCountByType.newCards,
          count: this.item?.count_new_cards
        },
      }
    },
    cssLeftRight() {
      const theItem = this.theItem;
      let left = 'bg-sp-200 hover:bg-sp-300 ';
      let right = 'bg-sp-100 ';
      if (theItem.itemType === 'deck') {
        left = 'bg-blue-200 hover:bg-blue-300 ';
        right = 'bg-blue-100 ';
      } else if (theItem.itemType === 'topic') {
        left = 'bg-amber hover:bg-gray-300 ';
        right = 'bg-amber-300';
      }
      return {
        left,
        right,
      }
    },
    studyActive(): boolean {
      const study = this.currentItemStudy;
      if (null !== study) {
        return study.active;
      }
      return false;
    },
    currentItemStudy(): null | _Study {
      if (Object.keys(this.item).includes('studies') && (this.item?.['studies'].length > 0)) {
        return this.item?.['studies'][0];
      }
      return null;
    }
  },
  created() {
    this.item = this.currentItem;
    window.addEventListener('resize', this.updateWindowWidth);
    // Make sure item has study.
    // Since a deck or topic can have multiple studies, we will add studies as an array.
    // Expecting just 1 study for now and for the current user.
    if (!Object.keys(this.item).includes('studies') || Object.keys(this.item).includes('studies') && this.item['studies'].length < 1) {
      this.item['studies'] = [];
    }
    if (!this.item['studies'].length) {
      this.item['studies'].push({
        deck: this.theItem.itemType === 'deck' ? this.theItem.item : null,
        topic: this.theItem.itemType === 'topic' ? this.theItem.item : null,
        tags: Array<_Tag>,
        tags_excluded: Array<_Tag>,
        all_tags: true,
        no_to_revise: 0,
        no_of_new: 0,
        no_on_hold: 0,
        revise_all: true,
        study_all_new: true,
        study_all_on_hold: true,
        active: false,
        id: 0,
        user: undefined
      } as _Study);
    }
  },
  methods: {
    countNewCards(cards: _Card[]): number {
      const newCardIds: number[] = this.userCards.newCardIds.value;
      let count = cards.reduce((acc, card) => {
        return acc + (newCardIds.includes(card.id as number) ? 1 : 0);
      }, 0);
      // console.log({count, cards, newCardIds, userCards: this.userCards});
      return count;
    },
    countRevisionCards(cards: _Card[]): number {
      const userCards = this.userCards;
      const revisionCardIds: number[] = userCards.revisionCardIds.value;
      return cards.reduce((acc, card) => {
        return acc + (revisionCardIds.includes(card.id as number) ? 1 : 0);
      }, 0);
    },
    countOnHoldCards(cards: _Card[]): number {
      const userCards = this.userCards;
      const onHoldCardIds: number[] = userCards.onHoldCardIds.value;
      return cards.reduce((acc, card) => {
        return acc + (onHoldCardIds.includes(card.id as number) ? 1 : 0);
      }, 0);
    },
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
    studyChanged() {
      if (Object.keys(this.item).includes('studies') && (this.item?.['studies'].length > 0)) {
        let study = this.item?.['studies'][0];
        let theStudy: _Study = JSON.parse(this.customStringify(study)) as _Study;
        setTimeout(() => {
          if (!this.isLoadingCards) {
            this.isLoadingCards = true;
            this
                .userDash.xhrCreateOrUpdateStudy(theStudy)
                .then((response) => {
                  this.userCards.loadUserCards();
                  this.isLoadingCards = false;
                });
          }
        }, 1000);
      }
    },
    showStudySettings() {
      const theItem = (this.item as _Deck | _Topic);
      this.studyToEdit = theItem.studies && theItem.studies.length > 0 ? theItem.studies[0] : null;
      this.viewDialogEditStudy = true;
    },
    updateWindowWidth() {
      this.windowWidth = window.innerWidth;
    },
    toggle() {
      this.uToggle.toggle(this.theItem.itemType + '-' + this.theItem.id);
      // this.showChildren = !this.showChildren;
    },
    viewCard() {
      // console.log('view card');
      if ('topic' !== this.theItem.itemType && 'deck' !== this.theItem.itemType) {
        return;
      }

      // The item must be studyable.
      if (!this.currentItemStudy) {
        return;
      }

      // The item's study must be active.
      if (!this.currentItemStudy.active) {
        return;
      }

      const cardsToStudy = (this.item as _Topic | _Deck).cards ?? [];
      if (cardsToStudy.length < 1) {
        return;
      }

      const theItem = (this.item as _Deck | _Topic);
      this.cardsToView = cardsToStudy;
      this.studyToEdit = theItem.studies && theItem.studies.length > 0 ? theItem.studies[0] : null;
      this.viewDialog = true;

      // this.cardsToView = (this.item as _Topic | _Deck).cards?.reduce((acc, card: _Card) => {
      //   return acc.concat(card);
      // }, [] as _Card[]);

      // setTimeout(() => this.openQuestionModal(), 1000);
    },
  },
  watch: {
    // watch for when viewDialog changes.
    viewDialog(newShow, oldShow) {
      if (!newShow) {
        this.userCards.loadUserCards();
      }
    },
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.updateWindowWidth);
  }
});

</script>
