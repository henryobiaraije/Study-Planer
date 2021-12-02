<?php


?>

<div class="menu-stats" >
	<div class="stats-forecast shadow p-2 m-2 mb-4 rounded" >

		<h4 class="text-center m-0 bold font-bold fs-4" >Forecast</h4 >
		<p class="text-center m-0 mb-2" >The number of reviews due in the future</p >
		<div v-if="!useStats.ajaxForecast.sending" class="stats-body" >
			<form @submit.prevent="useStats.xhrLoadForecast" class="select-month text-center mb-2" >
				<label class="m-2 cursor-pointer" >
					<input v-model="useStats.forecastSpan.value" name="forecast_span" value="one_month" type="radio" > <span >1 month</span ></label >
				<label class="m-2 cursor-pointer" >
					<input v-model="useStats.forecastSpan.value" name="forecast_span" value="three_month" type="radio" > <span >3 month</span ></label >
				<label class="m-2 cursor-pointer" >
					<input v-model="useStats.forecastSpan.value" name="forecast_span" value="one_year" type="radio" > <span >1 year</span ></label >
				<label class="m-2 cursor-pointer" >
					<input v-model="useStats.forecastSpan.value" name="forecast_span" value="all" type="radio" > <span >All</span ></label >
			</form >
			<div class="chart-forecast">
				<canvas class="m-auto" id="sp-chart-forecast" style="width:100%;max-width:700px"></canvas>
			</div>
			<div>
				<ul>
					<li class="flex"><div class="flex-1 text-right">Total: </div> <div class="flex-1 text-left font-bold pl-2">1008 reviews</div></li>
					<li class="flex"><div class="flex-1 text-right">Average: </div> <div class="flex-1 text-left font-bold pl-2">32.5 reviews/per day</div></li>
					<li class="flex"><div class="flex-1 text-right">Due tomorrow: </div> <div class="flex-1 text-left font-bold pl-2">75 cards</div></li>
				</ul>
			</div>
		</div >
		<div v-if="useStats.ajaxForecast.sending" style="text-align: center;flex: 12;font-size: 50px;" ><i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
