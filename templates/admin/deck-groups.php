<?php

	$active_url = 'admin.php?page=study-planner-deck-groups';
	$trash_url  = 'admin.php?page=study-planner-deck-groups&status=trash';
	$page_title = 'Deck Groups';
	$status     = filter_input( INPUT_GET, 'status' );
	$in_trash   = false;
	if ( 'trash' === $status ) {
		$in_trash   = true;
		$page_title .= " <span class='text-red-500'>(Trashed)</span> ";
	} else {
		$page_title .= " <span class='text-green-500'>(Active)</span> ";
	}
?>

<div class="deck-groups wrap" >

	<?php /***** Header ******/ ?>
	<!--	<editor-fold desc="Header">-->
	<ul class="subsubsub all-loaded w-full p-0" style="display: none" >
		<li ><h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 ></li >
		<li class="all" ><a href="<?php echo $active_url; ?>" class="" aria-current="page" >
				Active <span class="count" >({{deckGroups.totals.value.active}})</span ></a > |
		</li >
		<li class="publish" ><a href="<?php echo $trash_url; ?>" >
				Trashed <span class="count" >({{deckGroups.totals.value.trashed}})</span ></a >
		</li >
	</ul >
	<br />
	<!--	</editor-fold>-->

	<div class="all-loaded" style="display: none;" >
		<div class="flex flex-wrap gap-3 px-1 md:px-4" >
			<?php if ( ! $in_trash ): ?>
				<div class="form-area flex-1 md:flex-none  md:w-30 " >
					<form @submit.prevent="createDeckGroup()" class="bg-white rounded p-2" >
						<label class="tw-simple-input" >
							<span class="tw-title" >Deck group name</span >
							<input v-model="newDeckGroup.groupName.value" name="deck_group" required type="text" >
						</label >
						<ajax-action
								button-text="Create"
								css-classes="button"
								icon="fa fa-plus"
								:ajax="newDeckGroup.ajax.value" >
						</ajax-action >
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
						@on-page-change="deckGroups.onPageChange"
						@on-sort-change="deckGroups.onSortChange"
						@on-column-filter="deckGroups.onColumnFilter"
						@on-per-page-change="deckGroups.onPerPageChange"
						@on-selected-rows-change="deckGroups.onSelect"
						@on-search="deckGroups.onSearch"
				>
					<template slot="table-row" slot-scope="props" >
						<div v-if="props.column.field === 'name'" >
							<input @input="deckGroups.onEdit(props.row)" v-model="props.row.name" />
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
									@click="deckGroups.batchDelete()"
									:ajax="deckGroups.ajaxDelete.value" >
							</ajax-action-not-form >
						<?php else: ?>
							<ajax-action-not-form
									button-text="Trash Selected "
									css-classes="button button-link-delete"
									icon="fa fa-trash"
									@click="deckGroups.batchTrash()"
									:ajax="deckGroups.ajaxTrash.value" >
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
