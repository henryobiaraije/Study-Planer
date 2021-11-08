<?php


?>

<div class="deck-groups" >


	<div class="all-loaded" style="display: none;" >
		<div class="flex flex-wrap" >
			<div class="form-area flex-1" >
				<form @submit.prevent="createDeckGroup()" class="bg-white rounded p-2" >
					<label class="tw-simple-input" >
						<span class="tw-title" >Deck group name</span >
						<input v-model="newDeckGroup.groupName" class="w-full" type="text" >
					</label >
					<ajax-action
							button-text="Create"
							css-classes="button"
							icon="fa fa-plus"
							:ajax="newDeckGroup.ajax" >
					</ajax-action >
				</form >
			</div >
			<div class="table-area flex-1" >
				<table >
					<thead >
					<tr >
						<th >Id</th >
						<th ></th >
						<th ></th >
						<th ></th >
					</tr >
					</thead >
				</table >
			</div >
		</div >

	</div >


	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
