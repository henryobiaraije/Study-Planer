<?php

	$card_group = filter_input( INPUT_GET, 'card-group' );
	$page_title = 'New Card';
	$is_editing = false;
	if ( ! empty( $card_group ) ) {
		$page_title = 'Edit Card';
		$is_editing = true;
	}

?>

<div class="admin-basic-card wrap" >
	<!--	<editor-fold desc="Header">-->
	<h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 >
	<br />
	<!--	</editor-fold  desc="Header">-->

	<div class="all-loaded" style="display: none;" >
		<form v-if="showMain" @submit.prevent="basicCard.createOrUpdate()" class="rounded md:p-4 shadow bg-sp-300" style="max-width:1000px; margin: auto" >
			<label class="my-2 bg-white my-2 p-2 rounded shadow" >
				<span class="" >Name</span >
				<input v-model="basicCardGroup.name" required type="text" >
			</label >
			<div class="sp-wp-editor bg-white my-2 p-2 rounded shadow" >
				<span class="editor-title" >Question</span >
				<div class="editor-input" >
					<input-editor
							:value="basicCardGroup.whole_question"
							v-model="basicCardGroup.whole_question" ></input-editor >
				</div >
			</div >
			<div class="sp-wp-editor bg-white my-2 p-2 rounded shadow" >
				<span class="editor-title" >Answer</span >
				<div class="editor-input" >
					<input-editor v-model="basicCardItem.answer" ></input-editor >
				</div >
			</div >
			<div class="my-2 bg-white my-2 p-2 rounded shadow" >
				<span class="" >Scheduled at (optional)</span >
				<div class="border-1 p-1 px-2 mb-3 mt-0" >
					<label >
						<span > </span >
						<input v-model="basicCardGroup.scheduled_at" type="datetime-local" >
					</label >
				</div >
				<?php if ( $is_editing ): ?>
					<div class="flex bg-sp-100 rounded " >
						<div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full" >
							Created:
							<time-comp :time="basicCardGroup.created_at" ></time-comp >
						</div >
						<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
							Updated:
							<time-comp :time="basicCardGroup.updated_at" ></time-comp >
						</div >
						<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
							Trashed:
							<time-comp :time="basicCardGroup.deleted_at" ></time-comp >
						</div >
					</div >
				<?php endif; ?>
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
					button-text="<?php echo $is_editing ? 'Update' : 'Create' ?>"
					css-classes="button"
					icon="fa <?php echo $is_editing ? 'fa-upload' : 'fa-plus' ?>"
					:ajax="basicCard.ajaxCreate.value" >
			</ajax-action >
		</form >


	</div >
	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
	<div class="all-error" style="display: none" >
		<div class="text-red font-bold text-center bg-red-100 rounded p-4 m-auto text-red-500 " >
			Invalid Card Group
		</div >
	</div >
</div >



