<?php


	namespace StudyPlanner\Libs;


	class Settings {

		// Options
		public const OP_UNCATEGORIZED_DECK_GROUP_ID = 'sp_uncat_dkgrp_id';
		public const OP_UNCATEGORIZED_DECK_ID       = 'sp_uncat_dk_id';
		public const OP_DEFAULT_CARD_BG_IMAGE       = 'sp_df_cd_bg_img';

		// Cookies

		// Post types
		public const POST_TYPE_SEARCH_ENDPOINT = 'sbe_search_endpoint';

		// Post meta
		public const PM_ENDPOINT_NAME     = 'sbe_search_endpoint_name';
		public const PM_ENDPOINT_ENDPOINT = 'sbe_search_endpoint_endpoint';

		// User meta

		//
		public static function um_variable_link( $link_id ) {
			return "asl_variable_link_id_$link_id";
		}


	}