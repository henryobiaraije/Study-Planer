<?php

	$action = filter_input( INPUT_GET, 'action');
	$page_title = 'New Card';
	if('edit-deck' === $action){
		$page_title = 'Edit Card';
	}

?>


<div class="admin-cards" >
	<!--	<editor-fold desc="Header">-->
	<h1 class="wp-heading-inline" ><?php echo $page_title; ?> </h1 >
	<br />
	<!--	</editor-fold  desc="Header">-->
</div >
