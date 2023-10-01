<?php
/**
 * Template file for topics.
 */
$active_url = 'admin.php?page=study-planner-topics';
$trash_url  = 'admin.php?page=study-planner-topics&status=trash';
$page_title = 'Topics';
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

<div class="sp admin-topics wrap">
    <h1> Inside topic {{message}}</h1>


    <pick-image
            v-model='imageId'
            :default-image='""'
    ></pick-image>


	<?php /***** Header ******/ ?>
    <!--	<editor-fold desc="Header">-->
<!--    <ul class="subsubsub all-loaded w-full p-0" style="display: none">-->
<!--        <li><h1 class="wp-heading-inline">--><?php //echo $page_title; ?><!-- </h1></li>-->
<!--        <li class="all"><a href="--><?php //echo $active_url; ?><!--" class="" aria-current="page">-->
<!--                Active <span class="count">({{decks.totals.value.active}})</span></a> |-->
<!--        </li>-->
<!--        <li class="publish"><a href="--><?php //echo $trash_url; ?><!--">-->
<!--                Trashed <span class="count">({{decks.totals.value.trashed}})</span></a>-->
<!--        </li>-->
<!--    </ul>-->
    <br/>
    <!--	</editor-fold  desc="Header">-->

</div>
