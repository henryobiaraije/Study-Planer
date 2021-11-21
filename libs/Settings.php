<?php


	namespace StudyPlanner\Libs;


	class Settings {

		// Options
		public const OP_UNCATEGORIZED_DECK_GROUP_ID = 'sp_uncat_dkgrp_id';
		public const OP_UNCATEGORIZED_DECK_ID       = 'sp_uncat_dk_id';
		public const OP_DEFAULT_CARD_BG_IMAGE       = 'sp_df_cd_bg_img';

		// Cookies

		// Menu Slugs
		public const SLUG_DECKS       = 'study-planner-decks';
		public const SLUG_TAGS        = 'study-planner-tags';
		public const SLUG_DECK_GROUPS = 'study-planner-deck-groups';
		public const SLUG_ALL_CARDS   = 'study-planner-deck-cards';
		public const SLUG_BASIC_CARD   = 'study-planner-basic-card';
		public const SLUG_GAP_CARD   = 'study-planner-gap-card';

		// Post types
		public const POST_TYPE_SEARCH_ENDPOINT = 'sbe_search_endpoint';

		// Post meta
		public const PM_ENDPOINT_NAME     = 'sbe_search_endpoint_name';
		public const PM_ENDPOINT_ENDPOINT = 'sbe_search_endpoint_endpoint';

		// User meta
		public const UM_USER_TIMEZONE = 'sp_user_timezone';
		//
		public static function um_variable_link( $link_id ) {
			return "asl_variable_link_id_$link_id";
		}


	}