<template>
  <div class="one-accordion-item">
    <div class="accordion-header" :class="[top && !showChildren ? 'pb-2':'']">
      <!-- Header -->
      <div class="sp-deck-group-header border-b border-gray-100">
        <div @click="toggle()" class="sp-header-title flex">
          <div
              class="header-title-icon flex flex-1 justify-start items-center gap-2 cursor-pointer"
              :class="[cssLeftRight.left]"
          >
            <div v-if="'card_group' !== theItem.childrenType" class="sp-icon flex-initial px-2 text-gray-400">
              <v-icon v-if="!showChildren " left>
                mdi-chevron-down
              </v-icon>
              <v-icon v-if="showChildren" left>
                mdi-chevron-up
              </v-icon>
            </div>
            <div class="sp-name flex-1 font-medium text-black px-2 py-2 text-base">
              {{ theItem.name }}
            </div>
            <div class="header-counts flex-initial px-2 text-sm">
              {{ theItem.childrenLength }} {{ theItem.childrenTypeName }}{{ theItem.plural }}
            </div>
          </div>
          <div class="sp-deck-count flex-1 flex items-center justify-space-around py-1"
               :class="[cssLeftRight.right]"
          >
            <template v-for="(stat,statKey) in stats">
              <span class="on-hold bg-white font-semibold flex gap-2 justify-center ">
                <span class="text-sm px-2 py-1">{{ stat.title }}:</span>
                <span
                    class="text-sm px-2 rounded-full flex items-center font-semibold"
                    :class="[cssLeftRight.left]"
                >{{ stat.count }}</span>
              </span>
            </template>
          </div>
        </div>
      </div>
    </div>
    <!-- Body -->
    <div v-if="showChildren" class="accordion-body" :class="[top && showChildren ? 'pb-2' : '']">
      <template v-if="'topic' !== theItem.itemType">
        <template v-for="(child,childIndex) in theItem.children">
          <AccordionItem :user-cards="userCards" :item="child">
          </AccordionItem>
        </template>
      </template>
      <!--      <slot name="body"></slot>-->
    </div>

    <!-- Body -->
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
        <QuestionModal
            title="Study Cards"
            :cards="cardsToView"
            :show-only-answers="false"
            purpose="study"
        />
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
import type {_CardGroup, _Deck, _DeckGroup, _Topic} from "@/interfaces/inter-sp";
import QuestionModal from "@/vue-component/QuestionModal.vue";
import {_Card} from "@/interfaces/inter-sp";

export default defineComponent({
  name: 'AccordionItem',
  components: {QuestionModal, AjaxAction, CardSelector},
  props: {
    item: {
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
      showChildren: false,
      viewDialog: false,
      cardsToView: [] as _Card[],
    }
  },
  setup: (props, ctx) => {
    return {
      // userCards: useUserCards(),
      allCards: useAllCards(),
    }
  },
  computed: {
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
    cardsCountByType(): { newCards: number, revision: number, onHold: number } {
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
        counts.newCards = (item as _DeckGroup).decks.reduce((acc, deck) => {
          return acc + deck.topics.reduce((acc, topic) => {
            return acc + topic.card_groups.reduce((acc, cardGroup) => {
              return acc + cardGroup.cards.reduce((acc, card) => {
                return acc + (newCardIds.includes(card.id as number) ? 1 : 0);
              }, 0);
            }, 0);
          }, 0);
        }, 0);
        counts.revision = (item as _DeckGroup).decks.reduce((acc, deck) => {
          return acc + deck.topics.reduce((acc, topic) => {
            return acc + topic.card_groups.reduce((acc, cardGroup) => {
              return acc + cardGroup.cards.reduce((acc, card) => {
                return acc + (revisionCardIds.includes(card.id as number) ? 1 : 0);
              }, 0);
            }, 0);
          }, 0);
        }, 0);
        counts.onHold = (item as _DeckGroup).decks.reduce((acc, deck) => {
          return acc + deck.topics.reduce((acc, topic) => {
            return acc + topic.card_groups.reduce((acc, cardGroup) => {
              return acc + cardGroup.cards.reduce((acc, card) => {
                return acc + (holdCardIds.includes(card.id as number) ? 1 : 0);
              }, 0);
            }, 0);
          }, 0);
        }, 0);
      } else if ('deck' === theItem.itemType) {
        counts.newCards = (item as _Deck).topics.reduce((acc, topic) => {
          return acc + topic.card_groups.reduce((acc, cardGroup) => {
            return acc + cardGroup.cards.reduce((acc, card) => {
              return acc + (newCardIds.includes(card.id as number) ? 1 : 0);
            }, 0);
          }, 0);
        }, 0);
        counts.revision = (item as _Deck).topics.reduce((acc, topic) => {
          return acc + topic.card_groups.reduce((acc, cardGroup) => {
            return acc + cardGroup.cards.reduce((acc, card) => {
              return acc + (revisionCardIds.includes(card.id as number) ? 1 : 0);
            }, 0);
          }, 0);
        }, 0);
        counts.onHold = (item as _Deck).topics.reduce((acc, topic) => {
          return acc + topic.card_groups.reduce((acc, cardGroup) => {
            return acc + cardGroup.cards.reduce((acc, card) => {
              return acc + (holdCardIds.includes(card.id as number) ? 1 : 0);
            }, 0);
          }, 0);
        }, 0);
      } else if ('topic' === theItem.itemType) {
        counts.newCards = (item as _Topic).card_groups.reduce((acc, cardGroup) => {
          return acc + cardGroup.cards.reduce((acc, card) => {
            return acc + (newCardIds.includes(card.id as number) ? 1 : 0);
          }, 0);
        }, 0);
        counts.revision = (item as _Topic).card_groups.reduce((acc, cardGroup) => {
          return acc + cardGroup.cards.reduce((acc, card) => {
            return acc + (revisionCardIds.includes(card.id as number) ? 1 : 0);
          }, 0);
        }, 0);
        counts.onHold = (item as _Topic).card_groups.reduce((acc, cardGroup) => {
          return acc + cardGroup.cards.reduce((acc, card) => {
            return acc + (holdCardIds.includes(card.id as number) ? 1 : 0);
          }, 0);
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
      };
    },
    stats() {
      return {
        onHold: {
          title: 'On hold',
          count: this.cardsCountByType.onHold,
        },
        revision: {
          title: 'Revision',
          count: this.cardsCountByType.revision,
        },
        newCards: {
          title: 'New Cards',
          count: this.cardsCountByType.newCards,
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
        left = 'bg-amber-accent-1 hover:bg-gray-300 ';
        right = 'bg-amber ';
      }
      return {
        left,
        right,
      }
    },
  },
  created() {
  },
  methods: {
    toggle() {
      this.showChildren = !this.showChildren;
      this.viewCard();
      // const el = document.querySelector(selector);
      // if (el) {
      //   el.style.display = (el.style.display === 'none') ? 'block' : 'none';
      // }
    },
    viewCard(): _CardGroup[] {
      if ('topic' !== this.theItem.itemType) {
        return;
      }
      // this.cardsToView = topic.card_groups.
      this.viewDialog = true;
      this.cardsToView = (this.item as _Topic).card_groups.reduce((acc, cardGroup) => {
        return acc.concat(cardGroup.cards);
      }, [] as _Card[]);
      // setTimeout(() => this.openQuestionModal(), 1000);
    },
  },
  watch: {
    // watch for when viewDialog changes.
    viewDialog(newShow, oldShow) {
      if (!newShow) {
        this.userCards.loadUserCards();
      }
    }
  }
});

</script>
