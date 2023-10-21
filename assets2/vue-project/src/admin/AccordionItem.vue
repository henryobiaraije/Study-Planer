<template>
  <div class="one-accordion-item">
    <div class="accordion-header" :class="[top && !showChildren ? 'pb-2':'']">
      <!-- Header -->
      <div class="sp-deck-group-header">
        <div @click="toggle('.decks-'+theItem.id)" class="sp-header-title flex">
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
          <div class="sp-deck-count bg-sp-100 flex-1 flex items-center justify-space-around py-1"
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
      <template v-for="(child,childIndex) in theItem.children">
        <AccordionItem :item="child">
        </AccordionItem>
      </template>
      <!--      <slot name="body"></slot>-->
    </div>

  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useUserCards from "@/composables/useUserCards";
import useAllCards from "@/composables/useAllCards";
import type {_CardGroup, _Deck, _DeckGroup, _Topic} from "@/interfaces/inter-sp";

export default defineComponent({
  name: 'AccordionItem',
  components: {AjaxAction, CardSelector},
  props: {
    item: {
      type: Object as () => _DeckGroup | _Deck | _Topic | _CardGroup,
      required: true,
    },
    top: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      showChildren: false,
    }
  },
  setup: (props, ctx) => {
    return {
      userCards: useUserCards(),
      allCards: useAllCards(),
    }
  },
  computed: {
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
        'card_groups': 'cards',
      };
      let childrenTypeName = childrenMapNames[Object.keys(item).find(key => key in childrenMapNames)] || 'card_group';

      // console.log({childrenType: childrenType, map: childrenMap, item});

      let childrenLength = item[childrenType + 's'].length;
      if ('card_groups' === childrenType) {
        childrenLength = item[childrenType + 's'].reduce((acc, cardGroup) => {
          return acc + cardGroup.cards.length;
        }, 0);
      }

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
          count: 0,
        },
        revision: {
          title: 'Revision',
          count: 0,
        },
        newCards: {
          title: 'New Cards',
          count: 0,
        },
      }
    },
    cssLeftRight() {
      const theItem = this.theItem;
      let left = 'bg-sp-200 hover:bg-sp-300 ';
      let right = 'bg-sp-100 ';
      if (theItem.itemType === 'deck') {
        left = 'bg-blue-200 hover:bg-blue-200 ';
        right = 'bg-blue-100 ';
      } else if (theItem.itemType === 'topic') {
        left = 'bg-gray-200 hover:bg-gray-200 ';
        right = 'bg-gray-000 ';
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
    toggle(selector: string) {
      this.showChildren = !this.showChildren;
      // const el = document.querySelector(selector);
      // if (el) {
      //   el.style.display = (el.style.display === 'none') ? 'block' : 'none';
      // }
    },
  }
});

</script>
