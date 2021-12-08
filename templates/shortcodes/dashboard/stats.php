<?php


?>

<div class="menu-stats">
    <div class="stats-forecast ">
        <div class="stats-body">

            <?php /**** Chart Added  *****/ ?>
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
                            <div class="flex-1 text-left font-bold pl-2">{{useStats.statsChartAdded.value.total_new_cards}} cards</div>
                        </li>
                        <li class="flex">
                            <div class="flex-1 text-right">Average:</div>
                            <div class="flex-1 text-left font-bold pl-2">{{useStats.statsChartAdded.value.average_new_cards_per_day}} cards/day</div>
                        </li>
                    </ul>
                </div>
                <div v-if="useStats.ajaxChartAdded.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                            class="fa fa-spin fa-spinner"></i></div>
            </div>

            <?php /**** Review Time  *****/ ?>
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
                                {{useStats.statsReviewTime.value.days_studied_percent}}%
                                ({{useStats.statsReviewTime.value.days_studied_count}} of
                                {{useStats.statsReviewTime.value.total_days}})
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-1 text-right">Total:</div>
                            <div class="flex-1 text-left font-bold pl-2"> {{useStats.statsReviewTime.value.formed_total_time}}</div>
                        </li>
                        <li class="flex">
                            <div class="flex-1 text-right">Average for day studied:</div>
                            <div class="flex-1 text-left font-bold pl-2">{{useStats.statsReviewTime.value.average_time_for_days_studied_minutes}}
                                minutes/day
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-1 text-right">If you studied everyday:</div>
                            <div class="flex-1 text-left font-bold pl-2">{{useStats.statsReviewTime.value.average_time_if_studied_every_day_minutes}}
                                minutes/day
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-1 text-right">Average Answer Time:</div>
                            <div class="flex-1 text-left font-bold pl-2">
                                {{useStats.statsReviewTime.value.average_answered_cards_per_seconds_time}}s
                                ({{useStats.statsReviewTime.value.average_answer_cards_per_minute}} cards/minute)
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-if="useStats.ajaxReviewTime.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                            class="fa fa-spin fa-spinner"></i></div>
            </div>

            <?php /**** Chart Review Count  *****/ ?>
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
                                    {{useStats.statsReview.value.days_studied_percent}}%
                                    ({{useStats.statsReview.value.days_studied_count}} of
                                    {{useStats.statsReview.value.total_days}})
                                </div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">Total:</div>
                                <div class="flex-1 text-left font-bold pl-2">
                                    {{useStats.statsReview.value.total_reviews}} reviews
                                </div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">Average for day studied:</div>
                                <div class="flex-1 text-left font-bold pl-2">{{useStats.statsReview.value.average}} reviews/day</div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">If you studied everyday:</div>
                                <div class="flex-1 text-left font-bold pl-2">{{useStats.statsReview.value.average_if_studied_per_day}} reviews/day
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div v-if="useStats.ajaxReview.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                            class="fa fa-spin fa-spinner"></i></div>
            </div>

            <?php /**** Chart Forecast   *****/ ?>
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
                                    {{useStats.statsForecast.value.total_reviews}} reviews
                                </div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">Average:</div>
                                <div class="flex-1 text-left font-bold pl-2">{{useStats.statsForecast.value.average}}
                                    reviews/per day
                                </div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">Due tomorrow:</div>
                                <div class="flex-1 text-left font-bold pl-2">
                                    {{useStats.statsForecast.value.due_tomorrow}} cards
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div v-if="useStats.ajaxForecast.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                            class="fa fa-spin fa-spinner"></i></div>
            </div>


        </div>
    </div>
</div>
