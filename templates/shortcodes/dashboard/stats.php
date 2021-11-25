<?php


?>

<div class="menu-stats" >
	<div class="stats-forecast shadow p-2 m-2 mb-4 rounded" >

		<h4 class="text-center m-0 bold font-bold fs-4" >Forecast</h4 >
		<p class="text-center m-0 mb-2" >The number of reviews due in the future</p >
		<div v-if="!stats.ajaxForecast.sending" class="stats-body" >
			<form @click.prevent="stats.xhrLoadForecast" class="select-month text-center mb-2" >
				<label class="m-2 cursor-pointer" >
					<input v-model="stats.forecastSpan.value" name="forecast_span" value="one_month" type="radio" > <span >1 month</span ></label >
				<label class="m-2 cursor-pointer" >
					<input v-model="stats.forecastSpan.value" name="forecast_span" value="three_month" type="radio" > <span >3 month</span ></label >
				<label class="m-2 cursor-pointer" >
					<input v-model="stats.forecastSpan.value" name="forecast_span" value="one_year" type="radio" > <span >1 year</span ></label >
				<label class="m-2 cursor-pointer" >
					<input v-model="stats.forecastSpan.value" name="forecast_span" value="all" type="radio" > <span >All</span ></label >
			</form >
		</div >
		<div v-if="stats.ajaxForecast.sending" style="text-align: center;flex: 12;font-size: 50px;" ><i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
