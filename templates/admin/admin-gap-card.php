<?php
	/**
	 * Gap text template
	 */
	$card_group = filter_input( INPUT_GET, 'card-group' );
	$page_title = 'New Gap Card';
	$is_editing = false;
	if ( ! empty( $card_group ) ) {
		$page_title = 'Edit Gap Card';
		$is_editing = true;
	}

?>

<div class="admin-gap-card wrap" >
	<span class="reset-vue" @click="resetVue()" ></span >
	<!--	<editor-fold desc="Header">-->
	<h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 >
	<br />
	<!--	</editor-fold  desc="Header">-->

	<div class="all-loaded" style="display: none;" >
		<form v-if="showMain" @submit.prevent="gapCard.createOrUpdate()"
		      class=" md:p-4  gap-4 flex flex-wrap"
		      style="margin: auto" >
			<div class="flex-1 " >
				<?php /**** Name ***/ ?>
				<div class="bg-sp-300 shadow rounded sm:p-2 md:p-4 mb-4" >
					<label class="my-2 bg-white my-2 p-2 rounded shadow" >
						<span class="" >Name</span >
						<input v-model="gapCardGroup.name" name="card_name" required type="text" >
					</label >
					<div class="sp-wp-editor bg-white my-2 p-2 rounded shadow" >
						<span class="editor-title" >Question</span >
						<div class="editor-input" >
							<input-editor
									:value="gapCardGroup.whole_question"
									v-model="gapCardGroup.whole_question" ></input-editor >
						</div >
					</div >
				</div >
				<?php /**** Cards formed ***/ ?>
				<div class="card-preview rounded-3 p-2 bg-sp-300 border-1 border-sp-200" >
					<h3 class="font-bold fs-5  my-2" >Cards formed ({{gapCard.items.value.length}})
						<i class="fa fa-redo fs-6 bg-white p-1 rounded-full hover:rotate-180 cursor-pointer" ></i ></h3 >
					<ul >
						<li v-for="(item,itemIndex) in gapCard.items.value"
						    :data-hash="item.hash"
						    class="bg-white p-2 rounded-3" >
							<ul >
								<li ><b >Question:</b >
									<div v-html="item.question" ></div >
								</li >
								<li ><b >Answer:</b >
									<div v-html="item.answer" ></div >
								</li >
							</ul >
						</li >
					</ul >
				</div >
			</div >
			<div class="sm:flex-1 md:flex-initial bg-sp-300 shadow rounded sm:p-2 md:p-4" style="max-width: 300px" >
				<ajax-action
						button-text="<?php echo $is_editing ? 'Update' : 'Create' ?>"
						css-classes="button"
						icon="fa <?php echo $is_editing ? 'fa-upload' : 'fa-plus' ?>"
						:ajax="gapCard.ajaxCreate.value" >
				</ajax-action >
				<?php /**** Scheduled ***/ ?>
				<div class="my-2 bg-white my-2 p-2 rounded shadow" >
					<span class="" >Scheduled at (optional)</span >
					<div class="border-1 p-1 px-2 mb-3 mt-0" >
						<label >
							<span > </span >
							<input v-model="gapCardGroup.scheduled_at" type="datetime-local" >
						</label >
					</div >
					<?php if ( $is_editing ): ?>
						<div class="flex flex-wrap bg-sp-100 rounded " >
							<div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full" >
								Created:
								<time-comp :time="gapCardGroup.created_at" ></time-comp >
							</div >

							<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
								Scheduled:
								<time-comp :time="gapCardGroup.scheduled_at" ></time-comp >
							</div >
							<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
								Updated:
								<time-comp :time="gapCardGroup.updated_at" ></time-comp >
							</div >
							<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
								Trashed:
								<time-comp :time="gapCardGroup.deleted_at" ></time-comp >
							</div >
						</div >
					<?php endif; ?>
				</div >
				<?php /**** Deck ***/ ?>
				<div class="bg-white my-2 p-2 rounded shadow" >
					<span >Deck </span >
					<vue-mulitiselect
							v-model="gapCardGroup.deck" :options="decks.searchResults.value"
							:multiple="false" :loading="decks.ajaxSearch.value.sending"
							:searchable="true" :allowEmpty="false" :close-on-select="true"
							:taggable="false" :createTag="false" @search-change="decks.search"
							placeholder="Deck" label="name" track-by="id"
					></vue-mulitiselect >
				</div >
				<?php /**** Tags ***/ ?>
				<div class="mt-2 mb-2 bg-white my-2 p-2 rounded shadow" >
					<span >Tags</span >
					<vue-mulitiselect
							v-model="gapCardGroup.tags" :options="searchTags.results.value" :multiple="true"
							:loading="searchTags.ajax.value.sending" :searchable="true" :close-on-select="true"
							:taggable="true" :createTag="false" @tag="searchTags.addTag"
							@search-change="searchTags.search" placeholder="Tags" label="name" track-by="id"
					></vue-mulitiselect >
				</div >
				<?php /**** Background Image ***/ ?>
				<div class="my-4 bg-white my-2 p-2 rounded shadow" >
					<span >Background Image</span >
					<label class="block mb-2" >
						<span >Set as Default</span >
						<input v-model="gapCard.setBgAsDefault.value" type="checkbox" >
					</label >
					<pick-image
							v-model="gapCardGroup.bg_image_id"
							:default-image="localize.default_bg_image"
					></pick-image >
				</div >
				<ajax-action
						button-text="<?php echo $is_editing ? 'Update' : 'Create' ?>"
						css-classes="button"
						icon="fa <?php echo $is_editing ? 'fa-upload' : 'fa-plus' ?>"
						:ajax="gapCard.ajaxCreate.value" >
				</ajax-action >
			</div >

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



