<?php
	/**
	 * Table text template
	 */
	$card_group = filter_input( INPUT_GET, 'card-group' );
	$page_title = 'New Gap Card';
	$is_editing = false;
	if ( ! empty( $card_group ) ) {
		$page_title = 'Edit Gap Card';
		$is_editing = true;
	}

?>


<div class="admin-table-card" >
	<span class="reset-vue" @click="resetVue()" ></span >
	<div class="all-loaded" style="display: none;" >
		<form v-if="showMain" @submit.prevent="useTableCard.createOrUpdate()"
		      class="md:p-4  gap-4 flex flex-wrap"
		      style="margin: auto" >
			<div class="flex-1 " >
				<div class="action-buttons mb-2" >
					<button @click="useTableCard._tAddColumn(null)" type="button" class="button" >Add Column</button >
					<button @click="useTableCard._tAddRow(null)" type="button" class="button" >Add Row</button >
				</div >
				<!--				<ul >-->
				<!--					<li v-for="(item,itemIndex) in useTableCard.tableItem.value" >-->
				<!--						{{item}}-->
				<!--					</li >-->
				<!--				</ul >-->
				<?php /**** Table display ***/ ?>
				<div class="bg-gray-300 shadow rounded sm:p-2 md:p-4 mb-4" >
					<table v-if="useTableCard.tableItem.value.length > 0" class="table gap-table shadow p-2 bg-gray-100 rounded" >
						<thead >
						<tr >
							<th v-for="(item,itemIndex) in useTableCard.tableItem.value[0]"
							    @dblclick="useTableCard._openTableActionModal(itemIndex,0)"
							    class="table-cell border-1 border-gray-200" >
								<input-editor
										:allow-empty="true"
										:value="item"
										v-model="useTableCard.tableItem.value[0][itemIndex]" >
								</input-editor >
								<div class="position-relative" >
									<span :id="'table-col-row-' + itemIndex  + '-' + 0" class="position-absolute top-0 right-0" ></span >
								</div >
							</th >
						</tr >
						</thead >
						<tbody >
						<tr v-for="(item,itemIndex) in useTableCard.tableItem.value"
						    v-if="itemIndex !== 0" >
							<td @dblclick="useTableCard._openTableActionModal(itemIndex2,itemIndex)"
							    v-for="(item2,itemIndex2) in useTableCard.tableItem.value[itemIndex]"
							    class="table-cell border-1 border-gray-200" >
								<input-editor
										:allow-empty="true"
										:value="item2"
										v-model="useTableCard.tableItem.value[itemIndex][itemIndex2]" >
								</input-editor >
								<div class="position-relative" >
									<span :id="'table-col-row-' + itemIndex2  + '-' + itemIndex" class="position-absolute top-0 right-0" ></span >
								</div >
							</td >
						</tr >
						</tbody >
					</table >
					<div class="table-action" id="table-action" style="display: none; z-index:2147483647" >
						<ul class="m-0 p-0 shadow rounded overflow-hidden bg-white" style="max-width: 180px;font-size: 14px;" >
							<li @click="useTableCard._insertRowBefore" class="" >Insert Row Before</li >
							<li @click="useTableCard._insertRowAfter" class="" >Insert Row After</li >
							<li @click="useTableCard._insertColumnBefore" class="" >Insert Column Before</li >
							<li @click="useTableCard._insertColumnAfter" class="" >Insert Column After</li >
							<li @click="useTableCard._deleteColumn" class="" >Delete Column</li >
							<li @click="useTableCard._deleteRow" class="" >Delete Row</li >
						</ul >
					</div >
					<div class="sp-wp-editor bg-white my-2 p-2 rounded shadow" >
						<span class="editor-title" >Question</span >
						<div class="editor-input" >
							<input-editor
									:value="tableCardGroup.whole_question"
									v-model="tableCardGroup.whole_question" ></input-editor >
						</div >
					</div >
				</div >
				<?php /**** Cards formed ***/ ?>
				<div class="card-preview rounded-3 p-2 bg-gray-300 border-1 border-gray-200" >
					<h3 class="font-bold fs-5  my-2" >Cards formed ({{useTableCard.items.value.length}})
						<i class="fa fa-redo fs-6 bg-white p-1 rounded-full hover:rotate-180 cursor-pointer" ></i ></h3 >
					<ul >
						<li v-for="(item,itemIndex) in useTableCard.items.value"
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
			<div class="sm:flex-1 md:flex-initial bg-gray-300 shadow rounded sm:p-2 md:p-4" style="max-width: 300px" >
				<ajax-action
						button-text="<?php echo $is_editing ? 'Update' : 'Create' ?>"
						css-classes="button"
						icon="fa <?php echo $is_editing ? 'fa-upload' : 'fa-plus' ?>"
						:ajax="useTableCard.ajaxCreate.value" >
				</ajax-action >
				<?php /**** Scheduled ***/ ?>
				<div class="my-2 bg-white my-2 p-2 rounded shadow" >
					<span class="" >Scheduled at (optional)</span >
					<div class="border-1 p-1 px-2 mb-3 mt-0" >
						<label >
							<span > </span >
							<input v-model="tableCardGroup.scheduled_at" type="datetime-local" >
						</label >
					</div >
					<?php if ( $is_editing ): ?>
						<div class="flex flex-wrap bg-gray-100 rounded " >
							<div class="rounded bg-white text-black flex-auto m-2 p-1 text-center md:w-full" >
								Created:
								<time-comp :time="tableCardGroup.created_at" ></time-comp >
							</div >

							<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
								Scheduled:
								<time-comp :time="tableCardGroup.scheduled_at" ></time-comp >
							</div >
							<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
								Updated:
								<time-comp :time="tableCardGroup.updated_at" ></time-comp >
							</div >
							<div class="rounded bg-white text-black flex-1 flex-auto m-2 p-1 text-center md:w-full" >
								Trashed:
								<time-comp :time="tableCardGroup.deleted_at" ></time-comp >
							</div >
						</div >
					<?php endif; ?>
				</div >
				<?php /**** Deck ***/ ?>
				<div class="bg-white my-2 p-2 rounded shadow" >
					<span >Deck </span >
					<vue-mulitiselect
							v-model="tableCardGroup.deck" :options="decks.searchResults.value"
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
							v-model="tableCardGroup.tags" :options="searchTags.results.value" :multiple="true"
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
						<input v-model="useTableCard.setBgAsDefault.value" type="checkbox" >
					</label >
					<pick-image
							v-model="tableCardGroup.bg_image_id"
							:default-image="localize.default_bg_image"
					></pick-image >
				</div >
				<ajax-action
						button-text="<?php echo $is_editing ? 'Update' : 'Create' ?>"
						css-classes="button"
						icon="fa <?php echo $is_editing ? 'fa-upload' : 'fa-plus' ?>"
						:ajax="useTableCard.ajaxCreate.value" >
				</ajax-action >
			</div >
		</form >
	</div >

	<?php /*** Modal table action ***/ ?>
	<!--	<div class="modal fade" id="modal-table-action" style="z-index:99999999;display: none" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >-->
	<!--		<div class="modal-dialog" style="" >-->
	<!--			<form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" >-->
	<!--				<!--				<div class="modal-header" >-->-->
	<!--				<!--					<h5 class="modal-title" id="exampleModalEdit424e" >-->-->
	<!--				<!--						<span @click.prevent="incrShowExtra" >Table action</span >-->-->
	<!--				<!--					</h5 >-->-->
	<!--				<!--					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >-->-->
	<!--				<!--				</div >-->-->
	<!--				<div class="modal-body" >-->
	<!--					<ul >-->
	<!--						<li >Insert Row Before</li >-->
	<!--						<li >Insert Row After</li >-->
	<!--						<li >Insert Column Before</li >-->
	<!--						<li >Insert Column After</li >-->
	<!--						<li >Delete Column</li >-->
	<!--						<li >Delete Row</li >-->
	<!--					</ul >-->
	<!--				</div >-->
	<!--				<div class="modal-footer justify-center" >-->
	<!--					<div v-if="!userDash.showGrade.value" class="show-answer m-2" >-->
	<!--						<button @click="userDash._showAnswer()" type="button" class="sp-action-button" >Show Answer</button >-->
	<!--						<button @click="userDash._hold()" type="button" class="sp-action-button" >Hold</button >-->
	<!--					</div >-->
	<!--					<div v-if="userDash.showGrade.value" class="show-grade flex justify-center" >-->
	<!--						<div class="one-grade flex-initial px-2 mx-2" >-->
	<!--							<span class="grade-time block text-center" ></span >-->
	<!--							<button @click="userDash._markAnswer('again')" class="sp-action-button" type="button" >Again</button >-->
	<!--						</div >-->
	<!--						<div class="one-grade flex-initial mx-2" >-->
	<!--							<span class="grade-time block text-center" ></span >-->
	<!--							<button @click="userDash._markAnswer('hard')" type="button" class="sp-action-button" >Hard</button >-->
	<!--						</div >-->
	<!--						<div class="one-grade flex-initial mx-2" >-->
	<!--							<span class="grade-time block text-center" ></span >-->
	<!--							<button @click="userDash._markAnswer('good')" type="button" class="sp-action-button" >Good</button >-->
	<!--						</div >-->
	<!--						<div class="one-grade flex-initial mx-2" >-->
	<!--							<span class="grade-time block text-center" ></span >-->
	<!--							<button @click="userDash._markAnswer('easy')" type="button" class="sp-action-button" >Easy</button >-->
	<!--						</div >-->
	<!--					</div >-->
	<!--					<!--				<div v-if="showExtra > 5" >-->-->
	<!--					<!--					<ajax-action-not-form-->-->
	<!--					<!--							button-text="Load question"-->-->
	<!--					<!--							@click="userDash._getQuestions"-->-->
	<!--					<!--							css-classes="button"-->-->
	<!--					<!--							icon="fa fa-redo"-->-->
	<!--					<!--							:ajax="userDash.ajaxSaveStudy.value" >-->-->
	<!--					<!--					</ajax-action-not-form >-->-->
	<!--					<!--				</div >-->-->
	<!--				</div >-->
	<!--				--><?php ///** Debug Section */ ?>
	<!--				<section v-if="showExtra && null !== userDash.lastAnsweredDebugData.value" class="debug-section p-2" >-->
	<!--					<table class="table shadow rounded" >-->
	<!--						<tbody >-->
	<!--						<tr v-for="(value,key) in userDash.lastAnsweredDebugData.value" >-->
	<!--							<td >{{key}}</td >-->
	<!--							<td >{{value}}</td >-->
	<!--						</tr >-->
	<!--						</tbody >-->
	<!--					</table >-->
	<!--				</section >-->
	<!--			</form >-->
	<!--		</div >-->
	<!--	</div >-->

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
