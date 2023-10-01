<?php
/**
 * Template file for topics.
 */
$active_url = 'admin.php?page=study-planner-topics';
$trash_url = 'admin.php?page=study-planner-topics&status=trash';
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
    <h1> Inside topic {{message}}</h1>

</div>
