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
        <template v-if="show.progressChart">
          <div class="one-chart shadow p-2 m-2 mb-4 rounded position-relative min-h-[300px]">
            <h4 class="text-center m-0 bold font-bold fs-4">Progress Chart</h4>
            <div v-if="!useStats.ajaxProgressChart.sending" class="sp-slide-in">
              <form @submit.prevent="useStats._loadProgressChart"
                    class="select-month text-center mb-4 mt-2 sp-slide-in ">
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
                    <span class="text-green-600 font-semibold">{{
                        useStats.statsProgressChart.value.total_daily_average
                      }} cards</span>
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
                        class="text-green-600 font-semibold">{{
                        useStats.statsProgressChart.value.total_longest_streak
                      }} days</span>
                  </li>
                  <li class="min-w-[300px] text-center md:min-w-max md:mx-4 my-2 md:my-0">
                    <span class="font-semibold">Current Streak: </span>
                    <span
                        class="text-green-600 font-semibold">{{
                        useStats.statsProgressChart.value.total_current_streak
                      }} days</span>
                  </li>
                </ul>
              </div>
            </div>
            <div v-if="useStats.ajaxProgressChart.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- *** Chart Forecast   ***** -->
        <template v-if="show.chartForecast">
          <div class="one-chart shadow p-2 m-2 mb-4 rounded position-relative" style="min-height: 350px">
            <h4 class="text-center m-0 bold font-bold fs-4">Forecast</h4>
            <p class="text-center m-0 mb-2">The number of reviews due in the future</p>
            <div v-if="!useStats.ajaxForecast.sending" class="sp-slide-in">
              <form @submit.prevent="useStats._reloadForecast" class="select-month text-center mb-2">
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value"
                         name="forecast_span" value="one_month" type="radio"> <span>1 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value"
                         name="forecast_span" value="three_month" type="radio"> <span>3 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value"
                         name="forecast_span" value="one_year" type="radio"> <span>1 year</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value"
                         name="forecast_span" value="all" type="radio"> <span>All</span></label>
              </form>
              <div class="chart-forecast">
                <canvas class="m-auto" id="sp-chart-forecast" style="width:100%;max-width:700px"></canvas>
              </div>
              <div>
                <ul>
                  <li class="flex">
                    <div class="flex-1 text-right">Total:</div>
                    <div class="flex-1 text-left font-bold pl-2">
                      {{ useStats.statsForecast.value.total_reviews }} reviews
                    </div>
                  </li>
                  <li class="flex">
                    <div class="flex-1 text-right">Average:</div>
                    <div class="flex-1 text-left font-bold pl-2">{{ useStats.statsForecast.value.average }}
                      reviews/per day
                    </div>
                  </li>
                  <li class="flex">
                    <div class="flex-1 text-right">Due tomorrow:</div>
                    <div class="flex-1 text-left font-bold pl-2">
                      {{ useStats.statsForecast.value.due_tomorrow }} cards
                    </div>
                  </li>
                </ul>
              </div>
            </div>
            <div v-if="useStats.ajaxForecast.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Chart Review Count  ***** -->
        <template v-if="show.chartReviewCount">
          <div class="one-chart  shadow p-2 m-2 mb-4 rounded" style="min-height: 350px">
            <h4 class="text-center m-0 bold font-bold fs-4">Review Count</h4>
            <p class="text-center m-0 mb-2">The number of questions you have answered</p>
            <div v-if="!useStats.ajaxReview.sending">
              <form @submit.prevent="useStats._reloadReviewCount"
                    class="select-month text-center mb-2 sp-slide-in">
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewCount" v-model="useStats.reviewCountSpan.value"
                         name="forecast_span" value="one_month" type="radio"> <span>1 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewCount" v-model="useStats.reviewCountSpan.value"
                         name="forecast_span" value="three_month" type="radio"> <span>3 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewCount" v-model="useStats.reviewCountSpan.value"
                         name="forecast_span" value="one_year" type="radio"> <span>1 year</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewCount" v-model="useStats.reviewCountSpan.value"
                         name="forecast_span" value="all" type="radio"> <span>All</span></label>
              </form>
              <div class="chart-review-count  sp-slide-in">
                <canvas class="m-auto" id="sp-chart-review-count" style="width:100%;max-width:700px"></canvas>
              </div>
              <div class=" sp-slide-in">
                <ul>
                  <li class="flex">
                    <div class="flex-1 text-right">Days studied:</div>
                    <div class="flex-1 text-left font-bold pl-2">
                      {{ useStats.statsReview.value.days_studied_percent }}%
                      ({{ useStats.statsReview.value.days_studied_count }} of
                      {{ useStats.statsReview.value.total_days }})
                    </div>
                  </li>
                  <li class="flex">
                    <div class="flex-1 text-right">Total:</div>
                    <div class="flex-1 text-left font-bold pl-2">
                      {{ useStats.statsReview.value.total_reviews }} reviews
                    </div>
                  </li>
                  <li class="flex">
                    <div class="flex-1 text-right">Average for day studied:</div>
                    <div class="flex-1 text-left font-bold pl-2">{{ useStats.statsReview.value.average }} reviews/day
                    </div>
                  </li>
                  <li class="flex">
                    <div class="flex-1 text-right">If you studied everyday:</div>
                    <div class="flex-1 text-left font-bold pl-2">
                      {{ useStats.statsReview.value.average_if_studied_per_day }} reviews/day
                    </div>
                  </li>
                </ul>
              </div>
            </div>
            <div v-if="useStats.ajaxReview.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Review Time  ***** -->
        <template v-if="show.chartReviewTime">
          <div class="one-chart  shadow p-2 m-2 mb-4 rounded" style="min-height: 350px">
            <h4 class="text-center m-0 bold font-bold fs-4">Review Time</h4>
            <p class="text-center m-0 mb-2">The time taken to answer the questions</p>
            <div v-if="!useStats.ajaxReviewTime.sending">
              <form @submit.prevent="useStats._reloadReviewTime"
                    class="select-month text-center mb-2 sp-slide-in">
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewTime" v-model="useStats.reviewTimeSpan.value"
                         name="forecast_span" value="one_month" type="radio"> <span>1 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewTime" v-model="useStats.reviewTimeSpan.value"
                         name="forecast_span" value="three_month" type="radio"> <span>3 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewTime" v-model="useStats.reviewTimeSpan.value"
                         name="forecast_span" value="one_year" type="radio"> <span>1 year</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadReviewTime" v-model="useStats.reviewTimeSpan.value"
                         name="forecast_span" value="all" type="radio"> <span>All</span></label>
              </form>
              <div class="chart-review-time">
                <canvas class="m-auto" id="sp-chart-review-time" style="width:100%;max-width:700px"></canvas>
              </div>
              <ul class="slide-in">
                <li class="flex">
                  <div class="flex-1 text-right">Days studied:</div>
                  <div class="flex-1 text-left font-bold pl-2">
                    {{ useStats.statsReviewTime.value.days_studied_percent }}%
                    ({{ useStats.statsReviewTime.value.days_studied_count }} of
                    {{ useStats.statsReviewTime.value.total_days }})
                  </div>
                </li>
                <li class="flex">
                  <div class="flex-1 text-right">Total:</div>
                  <div class="flex-1 text-left font-bold pl-2"> {{ useStats.statsReviewTime.value.formed_total_time }}
                  </div>
                </li>
                <li class="flex">
                  <div class="flex-1 text-right">Average for day studied:</div>
                  <div class="flex-1 text-left font-bold pl-2">
                    {{ useStats.statsReviewTime.value.average_time_for_days_studied_minutes }}
                    minutes/day
                  </div>
                </li>
                <li class="flex">
                  <div class="flex-1 text-right">If you studied everyday:</div>
                  <div class="flex-1 text-left font-bold pl-2">
                    {{ useStats.statsReviewTime.value.average_time_if_studied_every_day_minutes }}
                    minutes/day
                  </div>
                </li>
                <li class="flex">
                  <div class="flex-1 text-right">Average Answer Time:</div>
                  <div class="flex-1 text-left font-bold pl-2">
                    {{ useStats.statsReviewTime.value.average_answered_cards_per_seconds_time }}s
                    ({{ useStats.statsReviewTime.value.average_answer_cards_per_minute }} cards/minute)
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="useStats.ajaxReviewTime.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Chart Added  ***** -->
        <template v-if="show.chartAdded">
          <div class="one-chart  shadow p-2 m-2 mb-4 rounded" style="min-height: 350px">
            <h4 class="text-center m-0 bold font-bold fs-4">Added</h4>
            <p class="text-center m-0 mb-2">The number of new cards you have added</p>
            <div v-if="!useStats.ajaxChartAdded.sending">
              <form @submit.prevent="useStats._reloadChartAdded"
                    class="select-month text-center mb-2 sp-slide-in">
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartAdded" v-model="useStats.chartAddedTimeSpan.value"
                         name="forecast_span" value="one_month" type="radio"> <span>1 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartAdded" v-model="useStats.chartAddedTimeSpan.value"
                         name="forecast_span" value="three_month" type="radio"> <span>3 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartAdded" v-model="useStats.chartAddedTimeSpan.value"
                         name="forecast_span" value="one_year" type="radio"> <span>1 year</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartAdded" v-model="useStats.chartAddedTimeSpan.value"
                         name="forecast_span" value="all" type="radio"> <span>All</span></label>
              </form>
              <div class="chart-review-time">
                <canvas class="m-auto" id="sp-chart-chart-added" style="width:100%;max-width:700px"></canvas>
              </div>
              <ul class="slide-in">
                <li class="flex">
                  <div class="flex-1 text-right">Total:</div>
                  <div class="flex-1 text-left font-bold pl-2">{{ useStats.statsChartAdded.value.total_new_cards }}
                    cards
                  </div>
                </li>
                <li class="flex">
                  <div class="flex-1 text-right">Average:</div>
                  <div class="flex-1 text-left font-bold pl-2">
                    {{ useStats.statsChartAdded.value.average_new_cards_per_day }} cards/day
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="useStats.ajaxChartAdded.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Chart Interval  ***** -->
        <template v-if="show.chartInterval">
          <div class="one-chart  shadow p-2 m-2 mb-4 rounded" style="min-height: 350px">
            <h4 class="text-center m-0 bold font-bold fs-4">Intervals</h4>
            <p class="text-center m-0 mb-2">Delays until reviews are shown again</p>
            <div v-if="!useStats.ajaxChartInterval.sending">
              <form @submit.prevent="useStats._reloadChartInterval"
                    class="select-month text-center mb-2 sp-slide-in">
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartInterval" v-model="useStats.chartIntervalTimeSpan.value"
                         name="forecast_span" value="one_month" type="radio"> <span>1 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartInterval" v-model="useStats.chartIntervalTimeSpan.value"
                         name="forecast_span" value="three_month" type="radio"> <span>3 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartInterval" v-model="useStats.chartIntervalTimeSpan.value"
                         name="forecast_span" value="one_year" type="radio"> <span>1 year</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._reloadChartInterval" v-model="useStats.chartIntervalTimeSpan.value"
                         name="forecast_span" value="all" type="radio"> <span>All</span></label>
              </form>
              <div class="chart-review-time">
                <canvas class="m-auto" id="sp-chart-chart-interval" style="width:100%;max-width:700px"></canvas>
              </div>
              <ul class="slide-in">
                <li class="flex">
                  <div class="flex-1 text-right">Average Interval:</div>
                  <div class="flex-1 text-left font-bold pl-2">{{ useStats.statsChartInterval.value.day_diff_average }}
                    days
                  </div>
                  <!--                            <div class="flex-1 text-left font-bold pl-2">22 days</div>-->
                </li>
                <li class="flex">
                  <div class="flex-1 text-right">Longest Interval:</div>
                  <!--                            <div class="flex-1 text-left font-bold pl-2">22.2 months</div>-->
                  <div class="flex-1 text-left font-bold pl-2">{{ useStats.statsChartInterval.value.longest_interval }}
                    days
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="useStats.ajaxChartInterval.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Answer Buttons  ***** -->
        <template v-if="show.chartAnswerButtons">
          <div class="one-chart  shadow p-2 m-2 mb-4 rounded" style="min-height: 350px">
            <h4 class="text-center m-0 bold font-bold fs-4">Answer Buttons</h4>
            <p class="text-center m-0 mb-2">The number of times you have pressed each button.</p>
            <div v-if="!useStats.ajaxChartAnswerButtons.sending">
              <form @submit.prevent="useStats._reloadChartAnswerButtons"
                    class="select-month text-center mb-2 sp-slide-in">
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._loadChartAnswerButtons" v-model="useStats.chartAnswerButtonsTimeSpan.value"
                         name="forecast_span" value="one_month" type="radio"> <span>1 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._loadChartAnswerButtons" v-model="useStats.chartAnswerButtonsTimeSpan.value"
                         name="forecast_span" value="three_month" type="radio"> <span>3 month</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._loadChartAnswerButtons" v-model="useStats.chartAnswerButtonsTimeSpan.value"
                         name="forecast_span" value="one_year" type="radio"> <span>1 year</span></label>
                <label class="m-2 cursor-pointer">
                  <input @change="useStats._loadChartAnswerButtons" v-model="useStats.chartAnswerButtonsTimeSpan.value"
                         name="forecast_span" value="all" type="radio"> <span>All</span></label>
              </form>
              <div class="chart-review-time">
                <canvas class="m-auto" id="sp-chart-chart-answer-buttons" style="width:100%;max-width:700px"></canvas>
              </div>
              <ul class="slide-in flex" style="max-width: 80%;margin: auto;">
                <li class="slide-in flex-1">
                  <div class="flex-1 text-center"><span>Correct:</span>
                    <b>{{ useStats.statsChartAnserButtons.value.days.learning.correct_percent }}%</b>
                  </div>
                  <div class="flex-1 text-center font-bold pl-2">
                    ({{ useStats.statsChartAnserButtons.value.days.learning.total_correct }} of
                    {{ useStats.statsChartAnserButtons.value.days.learning.total }})
                  </div>
                </li>
                <li class="slide-in flex-1">
                  <div class=" text-center"><span>Correct: </span>
                    <b>{{ useStats.statsChartAnserButtons.value.days.y.correct_percent }}%</b>
                  </div>
                  <div class="flex-1 text-center font-bold pl-2">
                    ({{ useStats.statsChartAnserButtons.value.days.y.total_correct }} of
                    {{ useStats.statsChartAnserButtons.value.days.y.total }})
                  </div>
                </li>
                <li class="slide-in flex-1">
                  <div class="flex-1 text-center"><span>Correct: </span>
                    <b>{{ useStats.statsChartAnserButtons.value.days.m.correct_percent }}%</b>
                  </div>
                  <div class="flex-1 text-center font-bold pl-2">
                    ({{ useStats.statsChartAnserButtons.value.days.m.total_correct }} of
                    {{ useStats.statsChartAnserButtons.value.days.m.total }})
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="useStats.ajaxChartAnswerButtons.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Hourly breakdown  ***** -->
        <template v-if="show.chartHourlyBreakdown">
          <div class="one-chart  shadow p-2 m-2 mb-4 rounded">
            <h4 class="text-center m-0 bold font-bold fs-4">Hourly Breakdown</h4>
            <p class="text-center m-0 mb-2">Review success rate for each hour of the day.</p>
            <div v-if="!useStats.ajaxHourlyBreakdown.sending">
              <form @submit.prevent="useStats._reloadChartAnswerButtons"
                    class="select-month text-center mb-2 sp-slide-in">
                <label class="m-2 cursor-pointer border-1 border-gray-300 py-2 px-4 rounded">
                  <span class="font-bold">Select a date:</span>
                  <input @change="useStats._loadChartHourlyBreakDown" v-model="useStats.chartHourlyBreakdownDate.value"
                         class="border-1  px-2 border-gray-500"
                         max="<?php echo \StudyPlannerPro\Libs\Common::getDate(); ?>"
                         name="forecast_span" value="one_month" type="date"></label>
              </form>
              <div v-show="useStats.chartHourlyBreakdownDate.value.length > 3" class="chart-review-time"
                   style="min-height: 350px">
                <canvas class="m-auto" id="sp-chart-chart-hourly-breakdown" style="width:100%;max-width:700px"></canvas>
              </div>
            </div>
            <div v-if="useStats.ajaxHourlyBreakdown.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

        <!-- **** Card Types  ***** -->
        <template v-if="show.chartCardTypes">
          <div class="one-chart shadow p-2 m-2 mb-4 rounded position-relative min-h-[300px]">
            <h4 class="text-center m-0 bold font-bold fs-4">Card Types</h4>
            <p class="text-center m-0 mb-2">The division of cards in your decks</p>
            <div v-if="!useStats.ajaxDeckCardTypeChart.sending">
              <div class="chart-review-count sp-slide-in">
                <canvas class="m-auto" id="sp-chart-card-types"
                        style="max-width:350px;max-width:700px"></canvas>
              </div>
              <div class=" sp-slide-in">
              </div>
            </div>
            <div v-if="useStats.ajaxDeckCardTypeChart.sending" style="text-align: center;flex: 12;font-size: 50px;">
              <i
                  class="fa fa-spin fa-spinner"></i></div>
          </div>
        </template>

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
    return {
      show: {
        progressChart: true,
        chartForecast: true,
        chartReviewCount: true,
        chartReviewTime: true,
        chartAdded: true,
        chartInterval: true,
        chartAnswerButtons: true,
        chartHourlyBreakdown: true,
        chartCardTypes: true,
      }
    }
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
    // this.useStats._loadAllStats();
    const nameAndFunction = [
      ['progressChart', this.useStats._loadProgressChart],
      ['chartForecast', this.useStats._loadForecast],
      ['chartReviewCount', this.useStats._loadReviewCount],
      ['chartReviewTime', this.useStats._loadReviewTime],
      ['chartAdded', this.useStats._loadChartAdded],
      ['chartInterval', this.useStats._loadChartIntervals],
      ['chartAnswerButtons', this.useStats._loadChartAnswerButtons],
      ['chartHourlyBreakdown', this.useStats._loadChartHourlyBreakDown],
      ['chartCardTypes', this.useStats._loadCardTypes],
    ];

    nameAndFunction.forEach((item) => {
      if (this.show[item[0]]) {
        (item[1] as Function)();
      }
    });

  },
  methods: {
    addCards() {
    }
  }
});

</script>
<!--<style type="scss">-->
<!--</style>-->