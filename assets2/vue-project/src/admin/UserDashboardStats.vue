<template>
  <div class="user-stats menu-stats pt-2 pb-6">
    <div class="sp-header pb-4 pt-2">
      <h2 class="text-xl font-semibold mb-0 text-sp-500 p-0">
        Stats
      </h2>
      <p class="text-base text-gray-500">Here is the analytics of your progress.</p>
    </div>
    <br/>

    <div class="stats-forecast ">
      <div class="stats-body">

        <!--**** Progress Chart   ***** -->
        <div class="one-chart shadow p-2 m-2 mb-4 rounded position-relative min-h-[300px]">
          <h4 class="text-center m-0 bold font-bold fs-4">Progress Chart</h4>
          <div v-if="!useStats.ajaxProgressChart.sending" class="sp-slide-in">
            <form @submit.prevent="useStats._loadProgressChart" class="select-month text-center mb-4 mt-2 sp-slide-in ">
              <label class="m-2 cursor-pointer border-1 border-gray-300 py-2 px-4 rounded">
                <span class="font-bold">Select a date:</span>
                <select @change="useStats._loadProgressChart" v-model="useStats.chartProgressChartYear.value"
                        class="border-1  px-2 border-gray-500">
                  <option value="">Select Year</option>
                  <option
                      v-for="(year,yearIndex) in years" :value="year"
                      :selected="currentYear === year"
                  >{{ year }}
                  </option>
                </select>
                <span class="ml-2 pl-2 border-left-">
                  <select v-model="useStats.progressSelectedColor.value"
                          class="border-1  px-2 border-gray-500">
                    <option value="">Color</option>
                    <option value="Green">Green</option>
                    <option value="Blue">Blue</option>
                    <option value="Red">Red</option>
                    <option value="Gray">Gray</option>
                  </select>
                </span>
              </label>
            </form>
            <div class="chart-forecast">
              <calendar-heatmap
                  :values="useStats.statsProgressChart.value.days_and_count"
                  :range-color="useStats._getProgressColorLegend()"
                  :max="12"
                  :end-date="useStats.chartProgressChartYear.value+'-12-31'"
              ></calendar-heatmap>
              <div class="m-auto" id="cal-heatmap" style="width:100%;max-width:700px"></div>
            </div>
            <div>
              <ul class="flex flex-wrap justify-content-center mt-4">
                <li class="min-w-[300px] text-center md:min-w-max md:mx-4 my-2 md:my-0">
                  <span class="font-semibold">Daily average: </span>
                  <span class="text-green-600 font-semibold">{{ useStats.statsProgressChart.value.total_daily_average }} cards</span>
                </li>
                <li class="min-w-[300px] text-center md:min-w-max md:mx-4 my-2 md:my-0">
                  <span class="font-semibold">Days learned: </span>
                  <span
                      class="text-green-600 font-semibold">{{
                      useStats.statsProgressChart.value.total_daily_percent
                    }}%</span>
                </li>
                <li class="min-w-[300px] text-center md:min-w-max md:mx-4 my-2 md:my-0">
                  <span class="font-semibold">Longest Streak: </span>
                  <span
                      class="text-green-600 font-semibold">{{ useStats.statsProgressChart.value.total_longest_streak }} days</span>
                </li>
                <li class="min-w-[300px] text-center md:min-w-max md:mx-4 my-2 md:my-0">
                  <span class="font-semibold">Current Streak: </span>
                  <span
                      class="text-green-600 font-semibold">{{ useStats.statsProgressChart.value.total_current_streak }} days</span>
                </li>
              </ul>
            </div>
          </div>
          <div v-if="useStats.ajaxProgressChart.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
              class="fa fa-spin fa-spinner"></i></div>
        </div>
      </div>
    </div>


  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import CardSelector from "@/admin/CardSelector.vue";
import AjaxAction from "@/vue-component/AjaxAction.vue";
import useStats from "@/composables/useStats";
import {CalendarHeatmap} from "vue3-calendar-heatmap";

export default defineComponent({
  name: 'UserDashboardAddCard',
  components: {CalendarHeatmap, AjaxAction, CardSelector},
  props: {},
  data() {
    return {}
  },
  setup: (props, ctx) => {
    return {
      // userCards: useUserCards(),
      // allCards: useAllCards(),
      useStats: useStats()
    }
  },
  computed: {
    years() {
      const years: string[] = [];
      for (let a = 0; a < 10; a++) {
        years.push((2021 + a).toString());
      }

      return years;
    },
    currentYear() {
      return new Date().getFullYear().toString();
    },
  },
  created() {
    this.useStats._loadAllStats();
  },
  methods: {
    addCards() {
    }
  }
});

</script>
<!--<style type="scss">-->
<!--</style>-->