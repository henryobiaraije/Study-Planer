<?php


namespace StudyPlannerPro\Libs;

class Settings {

	// Consts
	// public const MATURE_CARD_DAYS = 5; // todo, change to 21 in production

	// Options
	public const OP_UNCATEGORIZED_DECK_GROUP_ID = 'sp_uncat_dkgrp_id';
	public const OP_UNCATEGORIZED_DECK_ID = 'sp_uncat_dk_id';
	public const OP_DEFAULT_CARD_BG_IMAGE = 'sp_df_cd_bg_img';
	public const OPTION_MATURED_CARD_DAYS = 'ob_mat_card_days';

	// Cookies

	// Menu Slugs
	public const SLUG_DECKS = 'study-planner-pro-decks';
	public const SLUG_TAGS = 'study-planner-pro-tags';
	public const SLUG_DECK_GROUPS = 'study-planner-pro-deck-groups';
	public const SLUG_ALL_CARDS = 'study-planner-pro-deck-cards';
	public const SLUG_BASIC_CARD = 'study-planner-pro-basic-card';
	public const SLUG_GAP_CARD = 'study-planner-pro-gap-card';
	public const SLUG_ASSIGN_TOPICS = 'study-planner-pro-assign-topics';
	public const SLUG_TABLE_CARD = 'study-planner-pro-table-card';
	public const SLUG_IMAGE_CARD = 'study-planner-pro-image-card';
	public const SLUG_SETTINGS = 'study-planner-pro-settings';
	public const SLUG_TOPICS = 'study-planner-pro-topics';
	public const SLUG_COLLECTIONS = 'study-planner-pro-collections';

	// Post types
	public const POST_TYPE_SEARCH_ENDPOINT = 'sbe_search_endpoint';

	// Post meta
	public const PM_ENDPOINT_NAME = 'sbe_search_endpoint_name';
	public const PM_ENDPOINT_ENDPOINT = 'sbe_search_endpoint_endpoint';

	// User meta
	public const UM_USER_TIMEZONE = 'sp_user_timezone';
	public const UM_CURRENT_STUDY_DATE = 'sp_current_study_date';


	public static function um_variable_link( $link_id ) {
		return "asl_variable_link_id_$link_id";
	}


}
