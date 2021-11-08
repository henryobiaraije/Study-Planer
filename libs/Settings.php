<?php


	namespace StudyPlanner\Libs;


	class Settings {

		// Options

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