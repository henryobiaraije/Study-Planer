<?php

	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Settings;

	$active_url = Initializer::get_admin_url( Settings::SLUG_ALL_CARDS );
	$trash_url  = $active_url . '&status=trash';
	$page_title = 'Cards';
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


<div class="admin-all-cards" >
	<?php /***** Header ******/ ?>
	<!--	<editor-fold desc="Header">-->
	<ul class="subsubsub all-loaded w-full p-0" style="display: none" >
		<li ><h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 ></li >
		<li class="all" ><a href="<?php echo $active_url; ?>" class="" aria-current="page" >
				Active <span class="count" >({{allCards.totals.value.active}})</span ></a > |
		</li >
		<li class="publish" ><a href="<?php echo $trash_url; ?>" >
				Trashed <span class="count" >({{allCards.totals.value.trashed}})</span ></a >
		</li >
	</ul >
	<br />
	<!--	</editor-fold  desc="Header">-->

	<div class=" all-loaded" style="display: none;" >
		<div class="flex flex-wrap gap-3 px-1 md:px-4" >
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
						@on-page-change="allCards.onPageChange"
						@on-sort-change="allCards.onSortChange"
						@on-column-filter="allCards.onColumnFilter"
						@on-per-page-change="allCards.onPerPageChange"
						@on-selected-rows-change="allCards.onSelect"
						@on-search="allCards.onSearch"
				>
					<template slot="table-row" slot-scope="props" >
						<div v-if="props.column.field === 'name'" >
							<a :href="props.row.card_group_edit_url" ><span >{{props.row.name}}</span ></a >
							<?php if ( ! $in_trash ): ?>
								<div class="row-actions" >
									<span class="edit" >
									<a class="text-blue-500 font-bold" :href="props.row.card_group_edit_url" >
									Edit <i class="fa fa-pen-alt" ></i ></a >  </span >
								</div >
							<?php endif; ?>
						</div >
						<div v-else-if="props.column.field === 'type'" >
							{{props.row.card_type}}
						</div >
						<div v-else-if="props.column.field === 'total_cards'" >
							{{props.row.cards_count}}
						</div >
						<div v-else-if="props.column.field === 'deck'" >
							{{props.row.deck ? props.row.deck.name : ''}}
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
					<div slot="selected-row-actions" >
						<?php if ( $in_trash ): ?>
							<ajax-action-not-form
									button-text="Restore Selected  "
									css-classes="button button-secondary"
									icon="fa fa-redo"
									@click="allCards.batchRestore()"
									:ajax="allCards.ajaxRestore.value" >
							</ajax-action-not-form >
							<ajax-action-not-form
									button-text="Delete Selected Permanently "
									css-classes="button button-link-delete"
									icon="fa fa-trash"
									@click="allCards.batchDelete()"
									:ajax="allCards.ajaxDelete.value" >
							</ajax-action-not-form >
						<?php else: ?>
							<ajax-action-not-form
									button-text="Trash Selected "
									css-classes="button button-link-delete"
									icon="fa fa-trash"
									@click="allCards.batchTrash()"
									:ajax="allCards.ajaxTrash.value" >
							</ajax-action-not-form >
						<?php endif; ?>
					</div >
				</vue-good-table >
			</div >
		</div >
	</div >

	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
