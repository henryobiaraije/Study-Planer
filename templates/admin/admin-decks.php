<?php

	$active_url = 'admin.php?page=study-planner-decks';
	$trash_url  = 'admin.php?page=study-planner-decks&status=trash';
	$page_title = 'Decks';
	$status     = filter_input( INPUT_GET, 'status' );
	$in_trash   = false;
	$disabled   = '';
	if ( 'trash' === $status ) {
		$in_trash   = true;
		$disabled   = 'disabled';
		$page_title .= " <span class='text-red-500'>(Trashed)</span> ";
	} else {
		$page_title .= " <span class='text-green-500'>(Active)</span> ";
	}
?>

<div class="sp admin-decks wrap" >

	<?php /***** Header ******/ ?>
	<!--	<editor-fold desc="Header">-->
	<ul class="subsubsub all-loaded w-full p-0" style="display: none" >
		<li ><h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 ></li >
		<li class="all" ><a href="<?php echo $active_url; ?>" class="" aria-current="page" >
				Active <span class="count" >({{decks.totals.value.active}})</span ></a > |
		</li >
		<li class="publish" ><a href="<?php echo $trash_url; ?>" >
				Trashed <span class="count" >({{decks.totals.value.trashed}})</span ></a >
		</li >
	</ul >
	<br />
	<!--	</editor-fold  desc="Header">-->

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
			<div class="table-area flex-1" >
				<vue-good-table
						:columns="tableDataValue.columns"
						:mode="'remote'"
						:rows="tableDataValue.rows"
						:total-rows="tableDataValue.totalRecords"
						:compact-mode="true"
						:line-numbers="true"
						:is-loading="tableDataValue.isLoading"
						:pagination-options="tableDataValue.paginationOptions"
						:search-options="tableDataValue.searchOptions"
						:sort-options="tableDataValue.sortOption"
						:select-options="{ enabled: true, selectOnCheckboxOnly: true, }"
						:theme="''"
						@on-page-change="decks.onPageChange"
						@on-sort-change="decks.onSortChange"
						@on-column-filter="decks.onColumnFilter"
						@on-per-page-change="decks.onPerPageChange"
						@on-selected-rows-change="decks.onSelect"
						@on-search="decks.onSearch"
				>
					<template slot="table-row" slot-scope="props" >
						<div v-if="props.column.field === 'name'" >
							<input @input="decks.onEdit(props.row)"
								<?php echo $disabled ?>
								     :disabled="props.row.name === 'Uncategorized'"
								     v-model="props.row.name" />
							<?php if ( ! $in_trash ): ?>
								<div class="row-actions" >
									<span class="edit" >
									<a @click.prevent="decks.openEditModal(props.row,'#modal-edit')" class="text-blue-500 font-bold" href="#" >
									Edit <i class="fa fa-pen-alt" ></i ></a >  </span >
								</div >
							<?php endif; ?>
						</div >
						<div v-else-if="props.column.field === 'deck_group'" >
							{{props.row.deck_group ? props.row.deck_group.name : ''}}
						</div >
						<div v-else-if="props.column.field === 'tags'" >
							<ul class="" style="min-width: 100px;" >
								<li v-for="(item,itemIndex) in props.row.tags"
								    class="inline-flex items-center bg-gray-500 justify-center mr-1 px-2 py-1 text-xs font-bold leading-none text-white bg-gray-500 rounded" >{{item.name}}
								</li >
							</ul >
						</div >
						<span v-else-if="props.column.field === 'created_at'" >
							<time-comp :time="props.row.created_at" ></time-comp >
						</span >
						<span v-else-if="props.column.field === 'updated_at'" >
							<time-comp :time="props.row.updated_at" ></time-comp >
						</span >
						<span v-else >
				      {{props.formattedRow[props.column.field]}}
				    </span >
					</template >
					<!--					<template slot="table-row" slot-scope="props" >-->
					<!--						<input @input="tableOnEdit(props.row)" v-if="props.column.field === 'name'" v-model="props.row.name" />-->
					<!--						<input @input="tableOnEdit(props.row)" v-else-if="props.column.field ==='endpoint'" v-model="props.row.endpoint" />-->
					<!--					</template >-->
					<div slot="selected-row-actions" >
						<?php if ( $in_trash ): ?>
							<ajax-action-not-form
									button-text="Delete Selected Permanently "
									css-classes="button button-link-delete"
									icon="fa fa-trash"
									@click="decks.batchDelete()"
									:ajax="decks.ajaxDelete.value" >
							</ajax-action-not-form >
						<?php else: ?>
							<ajax-action-not-form
									button-text="Trash Selected "
									css-classes="button button-link-delete"
									icon="fa fa-trash"
									@click="decks.batchTrash()"
									:ajax="decks.ajaxTrash.value" >
							</ajax-action-not-form >
						<?php endif; ?>
					</div >
				</vue-good-table >
			</div >
		</div >
	</div >

	<?php /** Edit Modal */ ?>
	<div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >
		<div class="modal-dialog" >
			<form @submit.prevent="decks.updateEditing" class="modal-content" >
				<div class="modal-header" >
					<h5 class="modal-title" id="exampleModalEdit" >Edit Deck</h5 >
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >
				</div >
				<div v-if="null !== deckToEdit" class="modal-body" >
					<label class="tw-simple-input" >
						<span class="tw-title" >Deck</span >
						<input v-model="deckToEdit.name" name="deck_group" required type="text" >
					</label >
					<div >
						<span >Deck Group</span >
						<vue-mulitiselect
								v-model="deckToEdit.deck_group"
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
					<div >
						<span >Tags</span >
						<vue-mulitiselect
								v-model="deckToEdit.tags"
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
				</div >
				<div class="modal-footer" >
					<ajax-action
							button-text="Update"
							css-classes="button"
							icon="fa fa-save"
							:ajax="decks.ajaxUpdate.value" >
					</ajax-action >
				</div >
			</form >
		</div >
	</div >


	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >

</div >
