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

<div class="admin-deck wrap" >

	<?php /***** Header ******/ ?>


	<div class="all-loaded" style="display: none;" >
		<div class="flex flex-wrap gap-3 px-1 md:px-4" >
			Admin Decks
		</div >
	</div >


	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
