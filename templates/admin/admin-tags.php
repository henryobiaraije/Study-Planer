<?php
	$active_url = 'admin.php?page=study-planner-tags';
	$trash_url  = 'admin.php?page=study-planner-tags&status=trash';
	$page_title = 'Tags';
	$status     = filter_input( INPUT_GET, 'status' );
	$in_trash   = false;
	if ( 'trash' === $status ) {
		$in_trash   = true;
		$page_title .= " <span class='text-red-500'>(Trashed)</span> ";
	} else {
		$page_title .= " <span class='text-green-500'>(Active)</span> ";
	}
?>

<div class="admin-tags wrap" >

	<?php /***** Header ******/ ?>
	<!--	<editor-fold desc="Header">-->
	<ul class="subsubsub all-loaded w-full p-0" style="display: none" >
		<li ><h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 ></li >
		<li class="all" ><a href="<?php echo $active_url; ?>" class="" aria-current="page" >
				Active <span class="count" >({{tags.totals.value.active}})</span ></a > |
		</li >
		<li class="publish" ><a href="<?php echo $trash_url; ?>" >
				Trashed <span class="count" >({{tags.totals.value.trashed}})</span ></a >
		</li >
	</ul >
	<br />
	<!--	</editor-fold>-->

	<div class="all-loaded" style="display: none;" >
		<div class="flex flex-wrap gap-3 px-1 md:px-4" >
			<?php if ( ! $in_trash ): ?>
				<div class="form-area flex-1 md:flex-none  md:w-30 " >
					<form @submit.prevent="tags.create()" class="bg-white rounded p-2" >
						<label class="tw-simple-input" >
							<span class="tw-title" >Tag Name</span >
							<input v-model="tags.newName.value" name="tag_name" required type="text" >
						</label >
						<ajax-action
								button-text="Create"
								css-classes="button"
								icon="fa fa-plus"
								:ajax="tags.ajaxCreate.value" >
						</ajax-action >
					</form >
				</div >
			<?php endif; ?>
			<div class="table-area flex-1" >
			</div >
		</div >
	</div >

	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
