<?php

namespace StudyPlannerPro\Db\Migration;

global $wpdb;

if ( ! defined( 'SP_DB_PREFIX' ) ) {
	$db_prefix = $wpdb->prefix;
	$prefix    = $db_prefix . 'sp_';
	define( 'SP_DB_PREFIX', $prefix );
}

if ( ! defined( 'SP_TABLE_DECK_GROUPS' ) ) {
	define( 'SP_TABLE_DECK_GROUPS', SP_DB_PREFIX . 'deck_groups' );
}
if ( ! defined( 'SP_TABLE_TAGS' ) ) {
	define( 'SP_TABLE_TAGS', SP_DB_PREFIX . 'tags' );
}
if ( ! defined( 'SP_TABLE_TAGGABLES' ) ) {
	define( 'SP_TABLE_TAGGABLES', SP_DB_PREFIX . 'taggables' );
}
if ( ! defined( 'SP_TABLE_TAGGABLES_EXCLUDED' ) ) {
	define( 'SP_TABLE_TAGGABLES_EXCLUDED', SP_DB_PREFIX . 'taggables_excluded' );
}
if ( ! defined( 'SP_TABLE_DECKS' ) ) {
	define( 'SP_TABLE_DECKS', SP_DB_PREFIX . 'decks' );
}
if ( ! defined( 'SP_TABLE_CARD_GROUPS' ) ) {
	define( 'SP_TABLE_CARD_GROUPS', SP_DB_PREFIX . 'card_groups' );
}
if ( ! defined( 'SP_TABLE_CARDS' ) ) {
	define( 'SP_TABLE_CARDS', SP_DB_PREFIX . 'cards' );
}
if ( ! defined( 'SP_TABLE_STUDY' ) ) {
	define( 'SP_TABLE_STUDY', SP_DB_PREFIX . 'study' );
}
if ( ! defined( 'SP_TABLE_ANSWERED' ) ) {
	define( 'SP_TABLE_ANSWERED', SP_DB_PREFIX . 'answered' );
}
if ( ! defined( 'SP_TABLE_ANSWER_LOG' ) ) {
	define( 'SP_TABLE_ANSWER_LOG', SP_DB_PREFIX . 'answer_log' );
}
if ( ! defined( 'SP_TABLE_USERS' ) ) {
	define( 'SP_TABLE_USERS', $wpdb->prefix . 'users' );
}
if ( ! defined( 'SP_TABLE_STUDY_LOG' ) ) {
	define( 'SP_TABLE_STUDY_LOG', SP_DB_PREFIX . 'study_log' );
}
if ( ! defined( 'SP_TABLE_TOPICS' ) ) {
	define( 'SP_TABLE_TOPICS', SP_DB_PREFIX . 'topics' );
}
if ( ! defined( 'SP_TABLE_COLLECTIONS' ) ) {
	define( 'SP_TABLE_COLLECTIONS', SP_DB_PREFIX . 'collections' );
}
if ( ! defined( 'SP_TABLE_USER_CARDS' ) ) {
	define( 'SP_TABLE_USER_CARDS', SP_DB_PREFIX . 'user_cards' );
}


