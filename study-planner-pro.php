<?php
/**
 * Plugin Start File
 *
 * @package  StudyPlannerPro
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
 * @package           StudyPlannerPro
 *
 * @wordpress-plugin
 * Plugin Name:       Study Planner Pro
 * Plugin URI:        https://www.pereere.com/wordpress-plugins/study-planner-pro
 * Description:       Comes with admin dashboard to create deck groups, decks, cards. It also comes with an user dashboard where users can plan and study the cards with built in statistics. User Dashboard shortcode <code>[sp_pro_user_dashboard]</code>.
 * Version:           5.0.2
 * Author:            Pereere Codes (mpereere@gmail.com)
 * Author URI:        https://www.pereere.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       study-planner-pro
 * Domain Path:       /languages
 */

//phpinfo();
//return;
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class-initializer.php';


use StudyPlannerPro\Db\Initialize_Db;
use StudyPlannerPro\Initializer;

use function StudyPlannerPro\phinx_migrate;

//$phinx = new \Phinx\Console\PhinxApplication();
//$wrap  = new \Symfony\Component\Console\Input\StringInput( 'migrate' );
//try {
//	$phinx->run( $wrap );
//} catch ( Exception $e ) {
//	error_log( $e->getMessage() );
//}
phinx_migrate();


Initialize_Db::get_instance();
$initializer = Initializer::get_instance();
register_activation_hook( __FILE__, [ $initializer, 'on_activate' ] );
register_deactivation_hook( __FILE__, [ $initializer, 'on_deactivate' ] );
register_uninstall_hook( __FILE__, [ Initializer::class, 'on_uninstall' ] );

