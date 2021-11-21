<?php
?>


<div class="user-settings shadow p-4 rounded" >
	<form @submit.prevent="timezones.updateUserTimezone" class="sp-wrapper " >
		<label class="block mb-2" >
			<span class="block fs-5" >Time zone</span >
			<select v-model="timezones.userTimeZone.value" required class="px-2 py-2 fs-5" style="max-width: 300px" >
				<option value="" >Select your timezone</option >
				<option v-for="(value,name) in timezones.timezones.value" :value="name" >
					{{value}}
				</option >
			</select >
		</label >
		<ajax-action
				button-text="Study"
				css-classes="button"
				icon="fa fa-save"
				:ajax="timezones.ajax" >
		</ajax-action >
	</form >
</div >
