<?php


?>

<div class="menu-stats">
    <div class="stats-forecast ">
        <div class="stats-body">

            <?php /**** Chart Review Count  *****/ ?>
            <div class="one-chart  shadow p-2 m-2 mb-4 rounded"  style="min-height: 350px">
                <h4 class="text-center m-0 bold font-bold fs-4">Review Count</h4>
                <p class="text-center m-0 mb-2">The number of questions you have answered</p>
                <div v-if="!useStats.ajaxReview.sending">
                    <form @submit.prevent="useStats._reloadReviewCount" class="select-month text-center mb-2 sp-slide-in">
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
                                <div class="flex-1 text-right">Days of study:</div>
                                <div class="flex-1 text-left font-bold pl-2">88% (23 of 26)</div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">Total:</div>
                                <div class="flex-1 text-left font-bold pl-2">3206 reviews</div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">Average for day studied:</div>
                                <div class="flex-1 text-left font-bold pl-2">139.4 reviews/day</div>
                            </li>
                            <li class="flex">
                                <div class="flex-1 text-right">If you studied everyday:</div>
                                <div class="flex-1 text-left font-bold pl-2">123.3 reviews/day</div>
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

            <!--            --><?php ///**** Review Time  *****/ ?>
            <!--            <div v-if="null !== useStats.statsReview.value" class="one-chart hidden shadow p-2 m-2 mb-4 rounded">-->
            <!--                <div class="chart-review-time">-->
            <!--                    <canvas class="m-auto" id="sp-chart-review-time" style="width:100%;max-width:700px"></canvas>-->
            <!--                </div>-->
            <!--                <div>-->
            <!--                    <ul>-->
            <!--                        <li class="flex">-->
            <!--                            <div class="flex-1 text-right">Days of study:</div>-->
            <!--                            <div class="flex-1 text-left font-bold pl-2">88% (23 of 26)</div>-->
            <!--                        </li>-->
            <!--                        <li class="flex">-->
            <!--                            <div class="flex-1 text-right">Total:</div>-->
            <!--                            <div class="flex-1 text-left font-bold pl-2">9 hours</div>-->
            <!--                        </li>-->
            <!--                        <li class="flex">-->
            <!--                            <div class="flex-1 text-right">Average for day studied:</div>-->
            <!--                            <div class="flex-1 text-left font-bold pl-2">25.6 minutes/day</div>-->
            <!--                        </li>-->
            <!--                        <li class="flex">-->
            <!--                            <div class="flex-1 text-right">If you studied everyday:</div>-->
            <!--                            <div class="flex-1 text-left font-bold pl-2">22.6 minutes/day</div>-->
            <!--                        </li>-->
            <!--                        <li class="flex">-->
            <!--                            <div class="flex-1 text-right">Average Answer Time:</div>-->
            <!--                            <div class="flex-1 text-left font-bold pl-2">123.3 reviews/day</div>-->
            <!--                        </li>-->
            <!--                    </ul>-->
            <!--                </div>-->
            <!--                <br/> <br/>-->
            <!--            </div>-->
        </div>
    </div>
</div>
