<?php


?>

<div class="menu-stats" >
	<div class="stats-forecast shadow p-2 m-2 mb-4 rounded" >

		<h4 class="text-center m-0 bold font-bold fs-4" >Forecast</h4 >
		<p class="text-center m-0 mb-2" >The number of reviews due in the future</p >
		<div v-if="!useStats.ajaxForecast.sending" class="stats-body" >
			<div class="one-chart hidden" >
				<div class="chart-review-time" >
					<canvas class="m-auto" id="sp-chart-review-time" style="width:100%;max-width:700px" ></canvas >
				</div >
				<div >
					<ul >
						<li class="flex" >
							<div class="flex-1 text-right" >Days of study:</div >
							<div class="flex-1 text-left font-bold pl-2" >88% (23 of 26)</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Total:</div >
							<div class="flex-1 text-left font-bold pl-2" >9 hours</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Average for day studied:</div >
							<div class="flex-1 text-left font-bold pl-2" >25.6 minutes/day</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >If you studied everyday:</div >
							<div class="flex-1 text-left font-bold pl-2" >22.6 minutes/day</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Average Answer Time:</div >
							<div class="flex-1 text-left font-bold pl-2" >123.3 reviews/day</div >
						</li >
					</ul >
				</div >
				<br /> <br />
			</div >
			<div class="one-chart hidden" >
				<div class="chart-review-count" >
					<canvas class="m-auto" id="sp-chart-review-count" style="width:100%;max-width:700px" ></canvas >
				</div >
				<div >
					<ul >
						<li class="flex" >
							<div class="flex-1 text-right" >Days of study:</div >
							<div class="flex-1 text-left font-bold pl-2" >88% (23 of 26)</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Total:</div >
							<div class="flex-1 text-left font-bold pl-2" >3206 reviews</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Average for day studied:</div >
							<div class="flex-1 text-left font-bold pl-2" >139.4 reviews/day</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >If you studied everyday:</div >
							<div class="flex-1 text-left font-bold pl-2" >123.3 reviews/day</div >
						</li >
					</ul >
				</div >
				<br /> <br />
			</div >
			<div class="one-chart" >
				<form @submit.prevent="useStats._reloadForecast" class="select-month text-center mb-2" >
					<label class="m-2 cursor-pointer" >
						<input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value" name="forecast_span" value="one_month" type="radio" > <span >1 month</span ></label >
					<label class="m-2 cursor-pointer" >
						<input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value" name="forecast_span" value="three_month" type="radio" > <span >3 month</span ></label >
					<label class="m-2 cursor-pointer" >
						<input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value" name="forecast_span" value="one_year" type="radio" > <span >1 year</span ></label >
					<label class="m-2 cursor-pointer" >
						<input @change="useStats._reloadForecast" v-model="useStats.forecastSpan.value" name="forecast_span" value="all" type="radio" > <span >All</span ></label >
				</form >
				<div class="chart-forecast" >
					<canvas class="m-auto" id="sp-chart-forecast" style="width:100%;max-width:700px" ></canvas >
				</div >
				<div >
					<ul >
						<li class="flex" >
							<div class="flex-1 text-right" >Total:</div >
							<div class="flex-1 text-left font-bold pl-2" >1008 reviews</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Average:</div >
							<div class="flex-1 text-left font-bold pl-2" >32.5 reviews/per day</div >
						</li >
						<li class="flex" >
							<div class="flex-1 text-right" >Due tomorrow:</div >
							<div class="flex-1 text-left font-bold pl-2" >75 cards</div >
						</li >
					</ul >
				</div >
			</div >
		</div >
		<div v-if="useStats.ajaxForecast.sending" style="text-align: center;flex: 12;font-size: 50px;" ><i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
