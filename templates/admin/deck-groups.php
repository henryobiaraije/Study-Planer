<?php


?>

<div class="deck-groups wrap" >

	<?php /***** Header ******/ ?>
	<!--	<editor-fold desc="Header">-->
	<ul class="subsubsub" >
		<li ><h1 class="wp-heading-inline" >Active Deck Groups</h1 ></li >
		<li class="all" ><a href="edit.php?post_type=post" class="current" aria-current="page" >
				Active <span class="count" >(7,294)</span ></a > |
		</li >
		<li class="publish" ><a href="edit.php?post_status=publish&amp;post_type=post" >
				Trashed <span class="count" >(7,293)</span ></a > |
		</li >
	</ul >
	<br />
	<!--	</editor-fold>-->

	<div class="all-loaded" style="display: none;" >
		<div class="flex flex-wrap gap-3 px-1 md:px-4" >
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
			<div class="table-area flex-1" >
				<vue-good-table
						:columns="tableDataValue.columns"
						:mode="'remote'"
						:rows="tableDataValue.rows"
						:total-rows="tableDataValue.totalRecords"
						:compact-mode="true"
						:line-numbers="true"
						:is-loading="deckGroups.tableData.isLoading"
						:pagination-options="tableDataValue.paginationOptions"
						:search-options="tableDataValue.searchOptions"
						:sort-options="tableDataValue.sortOption"
						:select-options="{ enabled: true, selectOnCheckboxOnly: true, }"
						:theme="''"
						@on-page-change="deckGroups.onPageChange"
						@on-sort-change="deckGroups.onSortChange"
						@on-column-filter="deckGroups.onColumnFilter"
						@on-per-page-change="deckGroups.onPerPageChange"
						@on-selected-rows-change="deckGroups.onCheckboxSelected"
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
						<ajax-action-not-form
								button-text="Update Selected "
								css-classes="button button-primary"
								icon="fa fa-save"
								@click="xhrUpdateBatchEndpoints()"
								:ajax="ajax.update" >
						</ajax-action-not-form >
						<ajax-action-not-form
								button-text="Trash Selected "
								css-classes="button button-link-delete"
								icon="fa fa-trash"
								@click="xhrBatchTrashEndpoint()"
								:ajax="ajax.delete" >
						</ajax-action-not-form >
						<ajax-action-not-form
								button-text="Delete Selected Permanently "
								css-classes="button button-link-delete"
								icon="fa fa-trash"
								@click="xhrBatchDeleteEndpoint()"
								:ajax="ajax.delete" >
						</ajax-action-not-form >
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
