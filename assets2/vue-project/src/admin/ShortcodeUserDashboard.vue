<template>
  <div class="sp sp-sc-ud min-h-[60vh]">
    <!-- Tabs -->
    <div class="sp-tab flex gap-2 justify-center my-4 all-loaded">
      <div v-for="(item,menuIndex) in menus" class="sp-one-tab ">
        <a :href="getUrl(item.tag)" class="px-2 whitespace-nowrap md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer
			      text-decoration-none bg-sp-200"
           @click.prevent="gotoMenu(item.tag)"
           :class="[menu === item.tag ? 'font-bold bg-sp-500 text-white' : 'font-semibold text-sp-800']"
        >{{ item.title }}</a>
      </div>
      <!--      <div class="sp-one-tab ">-->
      <!--        <a :href="getUrl('stats')"-->
      <!--           class="px-2 whitespace-nowrap  md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 hover:text-white focus:text-white cursor-pointer text-decoration-none bg-sp-200"-->
      <!--           @click.prevent="gotoMenu('stats')"-->
      <!--           :class="[menu === 'stats' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Stats</a>-->
      <!--      </div>-->
      <!--      <div class="sp-one-tab ">-->
      <!--        <a :href="getUrl('settings')"-->
      <!--           class="px-2 whitespace-nowrap text-sp-800 md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer text-decoration-none bg-sp-200"-->
      <!--           @click.prevent="gotoMenu('settings')"-->
      <!--           :class="[menu === 'settings' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Settings</a>-->
      <!--      </div>-->
      <!--      <div class="sp-one-tab ">-->
      <!--        <a :href="getUrl('profile')"-->
      <!--           class="px-2 whitespace-nowrap text-sp-800 md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer text-decoration-none-->
      <!--			bg-sp-200"-->
      <!--           @click.prevent="gotoMenu('profile')"-->
      <!--           :class="[menu === 'profile' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Profile</a>-->
      <!--      </div>-->
    </div>

    <div v-for="(item,itemIndex) in menus" class="section-profile">
      <template v-if="item.tag === menu">
        <component :is="item.comp" v-bind="item.props"/>
      </template>
    </div>

    <!--    <?php /** Question display modal */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlannerPro\load_template('shortcodes/dashboard/study-complete-modal'); ?>-->
    <!--    </div>-->


    <!--    <?php /** Edit Study Modal */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlannerPro\load_template('shortcodes/dashboard/study-modal'); ?>-->
    <!--    </div>-->

    <!--    <?php /** Question display modal */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlannerPro\load_template('shortcodes/dashboard/question-modal'); ?>-->
    <!--    </div>-->

    <!--    <?php /** Modal Chart Deck's card type */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlannerPro\load_template('shortcodes/dashboard/modal-decks-card-types-chart'); ?>-->
    <!--    </div>-->

  </div>
  <!--  <ModalsContainer/>-->
  <hover-notifications></hover-notifications>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import HoverNotifications from "@/vue-component/HoverNotifications.vue";
import useUserProfile from "@/composables/useUserProfile";
import useTimezones from "@/composables/useTimezones";
import useUserDashboard from "@/composables/useUserDashboard";
import useTagSearch from "@/composables/useTagSearch";
import {CalendarHeatmap} from 'vue3-calendar-heatmap'
import useStats from "@/composables/useStats";
import UserDashboardProfile from "@/admin/UserDashboardProfile.vue";
import UserDashboardSettings from "@/admin/UserDashboardSettings.vue";
import {toast} from "vue3-toastify";
import UserDashboardAddCard from "@/admin/UserDashboardAddCard.vue";
import UserDashboardNewCards from "@/admin/UserDashboardNewCards.vue";
import UserDashboardRemoveCards from "@/admin/UserDashboardRemoveCards.vue";
import UserDashboardStudyDeck from "@/admin/UserDashboardStudyDeck .vue";

export default defineComponent({
  name: 'ShortcodeUserDashboard',
  components: {UserDashboardSettings, UserDashboardProfile, HoverNotifications, CalendarHeatmap},
  props: {},
  data() {
    return {
      menu: 'study-deck',
    }
  },
  setup: (props, ctx) => {
    return {
      searchTags: useTagSearch(false),
      userDash: useUserDashboard(),
      timezones: useTimezones(),
      userProfile: useUserProfile(),
      useStats: useStats()
    }
  },
  computed: {
    menus() {
      return [
        // {
        //   title: 'Deck Groups',
        //   tag: 'deck-groups'
        // },
        {
          title: 'Study Deck',
          tag: 'study-deck',
          comp: UserDashboardStudyDeck,
          props: {},
        },
        {
          title: 'Add Cards',
          tag: 'add-cards',
          comp: UserDashboardAddCard,
          props: {},
        },
        {
          title: 'New Cards',
          tag: 'new-cards',
          comp: UserDashboardNewCards,
          props: {},
        },
        {
          title: 'Remove Cards',
          tag: 'remove-cards',
          comp: UserDashboardRemoveCards,
          props: {},
        },
        {
          title: 'Stats',
          tag: 'stats',
          comp: null,
          props: {},
        },
        {
          title: 'Settings',
          tag: 'settings',
          comp: UserDashboardSettings,
          props: {
            userTimeZone: this.timezones.userTimeZone.value,
            theTimezones: this.timezones.timezones.value ? this.timezones.timezones.value : [],
            loading: this.timezones.ajax.sending
          }
        },
        {
          title: 'Profile',
          tag: 'profile',
          comp: UserDashboardProfile,
          props: {
            email: this.userProfile.profile.value ? this.userProfile.profile.value.user_email : 'loading...',
            username: this.userProfile.profile.value ? this.userProfile.profile.value.user_name : 'loading...'
          }
        }
      ] as {
        title: string,
        tag: string,
        comp?: any,
        props: { [key: string]: any }
      }[];
    }
  },
  created() {
    // this.userDash.load().then(() => {
    //
    // });
    jQuery('.all-loading').hide();
    jQuery('.all-loaded').show();
    this.generalInit();
    jQuery('head').append(`
    <style>
     @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;1,300&display=swap');
    </style>`
    );
  },
  methods: {
    generalInit() {
      const key = 'dashboard-page';
      const url = new URL(window.location.href);
      const searchParams = new URLSearchParams(url.search);
      const menu = searchParams.get(key);
      if (null !== menu && menu.length > 3) this.menu = menu;
      if (menu === 'settings') {
        this.timezones.loadTimezones();
      } else if (menu === 'stats') {
        // this.useStats._loadAllStats();
      } else if (menu === 'profile') {
        this.userProfile._loadProfile();
      }
    },
    getUrl(page: string): string {
      const currentUrl = window.location.href;
      const queryParams = new URLSearchParams(window.location.search);

      // Add or update the 'dashboard-page' query parameter
      queryParams.set('dashboard-page', page);
      return `${currentUrl.split('?')[0]}?${queryParams.toString()}`;
    },
    updateUrl(page: string): void {
      const currentUrl = window.location.href;
      const queryParams = new URLSearchParams(window.location.search);

      // Add or update the 'dashboard-page' query parameter
      queryParams.set('dashboard-page', page);
      const newUrl = `${currentUrl.split('?')[0]}?${queryParams.toString()}`;

      // Update the URL without redirecting
      history.pushState({}, '', newUrl);
    },
    gotoMenu(menu: string): void {
      this.menu = menu;
      this.updateUrl(menu);
      if (menu === 'settings') {
        this.timezones.loadTimezones();
      } else if (menu === 'stats') {
        // this.useStats._loadAllStats();
      } else if (menu === 'profile') {
        this.userProfile._loadProfile();
      }
    },
  }
});

</script>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->