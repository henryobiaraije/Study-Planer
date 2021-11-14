<?php

	$action     = filter_input( INPUT_GET, 'action' );
	$page_title = 'New Card';
	if ( 'card-edit' === $action ) {
		$page_title = 'Edit Card';
	}

?>


<div class="admin-basic-card wrap" >
	<!--	<editor-fold desc="Header">-->
	<h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 >
	<br />
	<!--	</editor-fold  desc="Header">-->

	<div class=" all-loaded" style="display: none;" >
		<form @submit.prevent="basicCard.createOrUpdate()" class="rounded p-2 shadow bg-gray-300" >
			<label class="my-2 bg-white my-2 p-2 rounded shadow" >
				<span class="" >Name</span >
				<input v-model="basicCardGroup.name" required type="text" >
			</label >
			<div class="sp-wp-editor bg-white my-2 p-2 rounded shadow" >
				<span class="editor-title" >Question</span >
				<div class="editor-input" >
					<input-editor v-model="basicCard.question" ></input-editor >
				</div >
			</div >
			<div class="sp-wp-editor bg-white my-2 p-2 rounded shadow" >
				<span class="editor-title" >Answer</span >
				<div class="editor-input" >
					<input-editor v-model="basicCard.answer" ></input-editor >
				</div >
			</div >
			<div class="my-2 bg-white my-2 p-2 rounded shadow" >
				<span class="" >Scheduled at</span >
				<div class="border-1 p-1 px-2 mb-3 mt-0" >
					<label >
						Now <input v-model="basicCard.scheduleNow.value" type="checkbox" >
					</label >
					<label v-if="!basicCard.scheduleNow.value" >
						<span > | Or Later</span >
						<input v-model="basicCardGroup.scheduled_at" required type="datetime-local" >
					</label >
				</div >
			</div >
			<label class="sp-wp-checkbox my-2 border-1 p-1 bg-white my-2 p-2 rounded shadow" >
				<span >Reverse</span >
				<input v-model="basicCardGroup.reverse" type="checkbox" class="" >
			</label >
			<div class="bg-white my-2 p-2 rounded shadow" >
				<span >Deck </span >
				<vue-mulitiselect
						v-model="basicCardGroup.deck"
						:options="decks.searchResults.value"
						:multiple="false"
						:loading="decks.ajaxSearch.value.sending"
						:searchable="true"
						:allowEmpty="false"
						:close-on-select="true"
						:taggable="false"
						:createTag="false"
						@search-change="decks.search"
						placeholder="Deck"
						label="name"
						track-by="id"
				></vue-mulitiselect >
			</div >
			<div class="mt-2 mb-2 bg-white my-2 p-2 rounded shadow" >
				<span >Tags</span >
				<vue-mulitiselect
						v-model="basicCardGroup.tags"
						:options="searchTags.results.value"
						:multiple="true"
						:loading="searchTags.ajax.value.sending"
						:searchable="true"
						:close-on-select="true"
						:taggable="true"
						:createTag="false"
						@tag="searchTags.addTag"
						@search-change="searchTags.search"
						placeholder="Tags"
						label="name"
						track-by="id"
				></vue-mulitiselect >
			</div >
			<div class="my-4 bg-white my-2 p-2 rounded shadow" >
				<span >Background Image</span >
				<label class="block mb-2" >
					<span >Set as Default</span >
					<input v-model="basicCard.setBgAsDefault.value" type="checkbox" >
				</label >
				<pick-image
						v-model="basicCardGroup.bg_image_id"
						:default-image="localize.default_bg_image"
				></pick-image >
			</div >
			<ajax-action
					button-text="Create"
					css-classes="button"
					icon="fa fa-plus"
					:ajax="basicCard.ajaxCreate.value" >
			</ajax-action >
		</form >
	</div >


	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >