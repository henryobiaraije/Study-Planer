<template>
  <div class="sp sp-sc-ud min-h-[60vh]">
    <!-- Tabs -->
    <div class="sp-tab flex gap-2 justify-center my-4 all-loaded">
      <div class="sp-one-tab ">
        <a :href="getUrl('deck-groups')" class="px-2 whitespace-nowrap md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer
			text-decoration-none bg-sp-200"
           @click.prevent="gotoMenu('deck-groups')"
           :class="[menu === 'deck-groups' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']"
        >Deck Groups</a>
      </div>
      <div class="sp-one-tab ">
        <a :href="getUrl('stats')"
           class="px-2 whitespace-nowrap  md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 hover:text-white focus:text-white cursor-pointer text-decoration-none bg-sp-200"
           @click.prevent="gotoMenu('stats')"
           :class="[menu === 'stats' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Stats</a>
      </div>
      <div class="sp-one-tab ">
        <a :href="getUrl('settings')"
           class="px-2 whitespace-nowrap text-sp-800 md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer text-decoration-none bg-sp-200"
           @click.prevent="gotoMenu('settings')"
           :class="[menu === 'settings' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Settings</a>
      </div>
      <div class="sp-one-tab ">
        <a :href="getUrl('profile')"
           class="px-2 whitespace-nowrap text-sp-800 md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer text-decoration-none
			bg-sp-200"
           @click.prevent="gotoMenu('profile')"
           :class="[menu === 'profile' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Profile</a>
      </div>
    </div>


    <!-- Deck groups -->
    <!--      <div v-if="menu === 'deck-groups'" class="section-deck-groups">-->
    <!--        <?php \StudyPlanner\load_template('shortcodes/dashboard/deck-groups'); ?>-->
    <!--      </div>-->
    <!--      <div v-if="menu === 'stats'" class="section-stats">-->
    <!--        <?php \StudyPlanner\load_template('shortcodes/dashboard/stats'); ?>-->
    <!--      </div>-->
    <div v-if="menu === 'settings'" class="section-settings">
      <UserDashboardSettings
          :user-time-zone="timezones.userTimeZone.value"
          :the-timezones="timezones.timezones.value ? timezones.timezones.value : []"
          :loading="timezones.ajax.sending"
      />
    </div>
    <div v-if=" menu===
      'profile'" class="section-profile">
      <UserDashboardProfile
          :email="userProfile.profile.value ? userProfile.profile.value.user_email : 'loading...'"
          :username="userProfile.profile.value ? userProfile.profile.value.user_name : 'loading...'"
      />
    </div>

    <button
        @click="testToast"
    >Test toast
    </button>
    <!--    <?php /** Question display modal */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlanner\load_template('shortcodes/dashboard/study-complete-modal'); ?>-->
    <!--    </div>-->


    <!--    <?php /** Edit Study Modal */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlanner\load_template('shortcodes/dashboard/study-modal'); ?>-->
    <!--    </div>-->

    <!--    <?php /** Question display modal */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlanner\load_template('shortcodes/dashboard/question-modal'); ?>-->
    <!--    </div>-->

    <!--    <?php /** Modal Chart Deck's card type */ ?>-->
    <!--    <div>-->
    <!--      <?php \StudyPlanner\load_template('shortcodes/dashboard/modal-decks-card-types-chart'); ?>-->
    <!--    </div>-->


  </div>
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

export default defineComponent({
  name: 'ShortcodeUserDashboard',
  components: {UserDashboardSettings, UserDashboardProfile, HoverNotifications, CalendarHeatmap},
  props: {},
  data() {
    return {
      menu: 'deck-groups',
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
  computed: {},
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
    testToast() {
      toast.success('test toast');
    }
  }
});

</script>
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->
<!--<style src="vue-multiselect/dist/vue-multiselect.css"></style>-->