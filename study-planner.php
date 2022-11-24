<?php
	/**
	 * Plugin Start File
	 *
	 * @package  StudyPlanner
	 * @version  1.0.0
	 */


	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	/**
	 * Plugin Description
	 *
	 * @link              https://www.pereere.com/
	 * @since             1.0.1
	 * @package           StudyPlanner
	 *
	 * @wordpress-plugin
	 * Plugin Name:       Study Planner
	 * Plugin URI:        https://www.pereere.com/wordpress-plugins/study-planner
	 * Description:       Comes with admin dashboard to create deck groups, decks, cards. It also comes with an user dashboard where users can plan and study the cards with built in statistics
	 * Version:           1.0.1
	 * Author:            Pereere Codes
	 * Author URI:        https://www.pereere.com/
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       study-planner
	 * Domain Path:       /languages
	 */

//    require_once 'languages.php';
	require_once __DIR__.'/vendor/autoload.php';
//	require_once __DIR__.'/bootstrap.php';
	require_once __DIR__.'/functions.php';
	require_once __DIR__.'/class-initializer.php';

	use StudyPlanner\Db\Initialize_Db;
	use StudyPlanner\Initializer;

	Initialize_Db::get_instance();
	$initializer = Initializer::get_instance();


	register_activation_hook( __FILE__, [ $initializer, 'on_activate' ] );
	register_deactivation_hook( __FILE__, [ $initializer, 'on_deactivate' ] );
	register_uninstall_hook( __FILE__, [ Initializer::class, 'on_uninstall' ] );




