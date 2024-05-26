<?php
namespace StudyPlannerPro\Db;
global $wpdb;
$prefix = 'sp_';
define( 'SP_DB_PREFIX', $prefix );
define( 'SP_TABLE_DECK_GROUPS', SP_DB_PREFIX . 'deck_groups' );
define( 'SP_TABLE_TAGS', SP_DB_PREFIX . 'tags' );
define( 'SP_TABLE_TAGGABLES', SP_DB_PREFIX . 'taggables' );
define( 'SP_TABLE_TAGGABLES_EXCLUDED', SP_DB_PREFIX . 'taggables_excluded' );
define( 'SP_TABLE_DECKS', SP_DB_PREFIX . 'decks' );
define( 'SP_TABLE_CARD_GROUPS', SP_DB_PREFIX . 'card_groups' );
define( 'SP_TABLE_CARDS', SP_DB_PREFIX . 'cards' );
define( 'SP_TABLE_STUDY', SP_DB_PREFIX . 'study' );
define( 'SP_TABLE_ANSWERED', SP_DB_PREFIX . 'answered' );
define( 'SP_TABLE_ANSWER_LOG', SP_DB_PREFIX . 'answer_log' );
define( 'SP_TABLE_USERS', $wpdb->prefix . 'users' );
define( 'SP_TABLE_STUDY_LOG', SP_DB_PREFIX . 'study_log' );
define( 'SP_TABLE_TOPICS', SP_DB_PREFIX . 'topics' );
define( 'SP_TABLE_COLLECTIONS', SP_DB_PREFIX . 'collections' );
define( 'SP_TABLE_USER_CARDS', SP_DB_PREFIX . 'user_cards' );


