<?php

	$active_url = 'admin.php?page=study-planner-decks';
	$trash_url  = 'admin.php?page=study-planner-decks&status=trash';
	$page_title = 'Decks';
	$status     = filter_input( INPUT_GET, 'status' );
	$in_trash   = false;
	if ( 'trash' === $status ) {
		$in_trash   = true;
		$page_title .= " <span class='text-red-500'>(Trashed)</span> ";
	} else {
		$page_title .= " <span class='text-green-500'>(Active)</span> ";
	}
?>

<div class="admin-decks wrap" >

	<?php /***** Header ******/ ?>


	<div class=" all-loaded" style="display: none;" >
		<div class="flex flex-wrap gap-3 px-1 md:px-4" >
			<?php if ( ! $in_trash ): ?>
				<div class="form-area flex-1 md:flex-none  md:w-30 " >
					<form @submit.prevent="decks.create()" class="bg-white rounded p-2" >
						<label class="tw-simple-input" >
							<span class="tw-title" >Deck </span >
							<input v-model="deckNew.name" name="deck" required type="text" >
						</label >
						<div >
							<span >Deck Group</span >
							<vue-mulitiselect
									v-model="deckNew.deckGroup"
									:options="deckGroups.searchResults.value"
									:multiple="false"
									:loading="deckGroups.ajaxSearch.value.sending"
									:searchable="true"
									:allowEmpty="false"
									:close-on-select="true"
									:taggable="false"
									:createTag="false"
									@search-change="deckGroups.search"
									placeholder="Deck Group"
									label="name"
									track-by="id"
							></vue-mulitiselect >
						</div >
						<div class="mt-2" >
							<span >Tags</span >
							<vue-mulitiselect
									v-model="deckNew.tags"
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
						<div class="mt-3" >
							<ajax-action
									button-text="Create"
									css-classes="button"
									icon="fa fa-plus"
									:ajax="decks.ajaxCreate.value" >
							</ajax-action >
						</div >
					</form >
				</div >
			<?php endif; ?>
		</div >
	</div >


	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
