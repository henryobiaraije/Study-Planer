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
		<form v-if="showMain" @submit.prevent="useTableCard._createOrUpdate()"
		      class="md:p-4  gap-4 flex flex-wrap"
		      style="margin: auto" >
			<div class="flex-1 " >
				<?php /**** Name ***/ ?>
				<div class="bg-sp-50 shadow rounded sm:p-2 md:p-4 mb-4" >
					<label class="my-2 bg-white my-2 p-2 rounded shadow" >
						<span class="" >Name</span >
						<input v-model="tableCardGroup.name" name="card_name" required type="text" >
					</label >
				</div >
				<div class="action-buttons mb-2" >
					<button @click="useTableCard._tAddColumn(null)" type="button" class="button" >Add Column</button >
					<button @click="useTableCard._tAddRow(null)" type="button" class="button" >Add Row</button >
				</div >
				<?php /**** Table display ***/ ?>
				<div class="bg-sp-300 shadow rounded sm:p-2 md:p-4 mb-4" >
					<table v-if="useTableCard.tableItem.value.length > 0" class="table gap-table shadow p-2 bg-sp-100 rounded" >
						<thead >
						<tr >
							<th v-for="(item,itemIndex) in useTableCard.tableItem.value[0]"
							    @dblclick="useTableCard._openTableActionModal(itemIndex,0)"
							    class="table-cell border-1 border-sp-200" >
								<input-editor
										:allow-empty="true"
										@input="useTableCard._refreshPreview"
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
						    :class="{'bg-gray-100' : (itemIndex / 2 > 0)}"
						    v-if="itemIndex !== 0" >
							<td @dblclick="useTableCard._openTableActionModal(itemIndex2,itemIndex)"
							    v-for="(item2,itemIndex2) in useTableCard.tableItem.value[itemIndex]"
							    class="table-cell border-1 border-sp-200" >
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
					<div class="table-action action-menu" id="table-action" style="display: none; z-index:2147483647" >
						<ul class="m-0 p-0 shadow rounded overflow-hidden bg-white" style="max-width: 180px;font-size: 14px;" >
							<li @click="useTableCard._insertRowBefore" class="" >Insert Row Before</li >
							<li @click="useTableCard._insertRowAfter" class="" >Insert Row After</li >
							<li @click="useTableCard._insertColumnBefore" class="" >Insert Column Before</li >
							<li @click="useTableCard._insertColumnAfter" class="" >Insert Column After</li >
							<li @click="useTableCard._deleteColumn" class="" >Delete Column</li >
							<li @click="useTableCard._deleteRow" class="" >Delete Row</li >
						</ul >
					</div >
				</div >
				<?php /**** Cards formed ***/ ?>
				<div class="card-preview rounded-3 p-2 bg-sp-300 border-1 border-sp-200" >
					<h3 class="font-bold fs-5  my-2" >Cards formed ({{useTableCard.items.value.length}})
						<i @click="useTableCard._refreshPreview" class="fa fa-recycle fs-6 bg-white p-1 rounded-full hover:rotate-180 cursor-pointer" ></i ></h3 >
					<ul >
						<li v-for="(item,itemIndex) in useTableCard.items.value"
						    :data-hash="item.hash"
						    class="bg-white p-2 rounded-3" >
							<ul >
								<li ><b >Question:</b >
									<table v-if="item.question.length > 0" class="table gap-table shadow p-2 bg-sp-100 rounded" >
										<thead >
										<tr >
											<th v-for="(item2,itemIndex2) in item.question[0]"
											    class="table-cell border-1 border-sp-200" >
												<div v-html="item2" ></div >
											</th >
										</tr >
										</thead >
										<tbody >
										<tr v-for="(item2,itemIndex2) in item.question"
										    :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
										    v-if="itemIndex2 !== 0" >
											<td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200" >
												<div v-html="item3" ></div >
											</td >
										</tr >
										</tbody >
									</table >
								</li >
								<li ><b >Answer:</b >
									<table v-if="item.question.length > 0" class="table gap-table shadow p-2 bg-sp-100 rounded" >
										<thead >
										<tr >
											<th v-for="(item2,itemIndex2) in item.answer[0]"
											    class="table-cell border-1 border-sp-200" >
												<div v-html="item2" ></div >
											</th >
										</tr >
										</thead >
										<tbody >
										<tr v-for="(item2,itemIndex2) in item.answer"
										    :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
										    v-if="itemIndex2 !== 0" >
											<td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200" >
												<div v-html="item3" ></div >
											</td >
										</tr >
										</tbody >
									</table >
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
						<div class="flex flex-wrap bg-sp-100 rounded " >
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
							required
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
