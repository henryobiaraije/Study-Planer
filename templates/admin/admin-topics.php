<?php
/**
 * Template file for topics.
 */
$active_url = 'admin.php?page=study-planner-pro-topics';
$trash_url = 'admin.php?page=study-planner-pro-topics&status=trash';
$page_title = 'Topics';
$status = filter_input(INPUT_GET, 'status');
$in_trash = false;
$disabled = '';
if ('trash' === $status) {
    $in_trash = true;
    $disabled = 'disabled';
    $page_title .= " <span class='text-red-500'>(Trashed)</span> ";
} else {
    $page_title .= " <span class='text-green-500'>(Active)</span> ";
}

?>

<div class="sp admin-topics wrap">

    <?php /***** Header ******/ ?>
    <!--	<editor-fold desc="Header">-->
    <ul class="subsubsub all-loaded w-full p-0" style="display: none">
        <li><h1 class="wp-heading-inline"><?php echo $page_title; ?> </h1></li>
        <li class="all"><a href="<?php echo $active_url; ?>" class="" aria-current="page">
                Active <span class="count">({{decks.totals.value.active}})</span></a> |
        </li>
        <li class="publish"><a href="<?php echo $trash_url; ?>">
                Trashed <span class="count">({{decks.totals.value.trashed}})</span></a>
        </li>
    </ul>
    <br/>
    <!--	</editor-fold  desc="Header">-->


    <div class='all-loading' style='width: 100%;height: 400px;display: flex;align-items: center;'>
        <div style='text-align: center;flex: 12;font-size: 50px;'>
            <i class='fa fa-spin fa-spinner'></i></div>
    </div>
</div>