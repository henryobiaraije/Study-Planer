<?php
/**
 * Db model for topics.
 */


namespace StudyPlanner\Pages;

if (!defined('ABSPATH')) {
    exit();
}

use StudyPlanner\Initializer;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Services\FileService;

/**
 * Class AdminTopics
 */
class AdminTopics
{
    /**
     * @var self $instance
     */
    private static $instance;

    /**
     * AdminAuth constructor.
     */
    private function __construct()
    {
    }

    public static function get_instance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        self::$instance->initialize();

        return self::$instance;
    }

    public function initialize(): void
    {
        add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
        add_action('init', array($this, 'init'));
    }

    public function init(): void
    {
        add_action('admin_menu', [$this, "add_admin_menu"], 11);
        add_action('in_admin_header', function () {
            remove_all_actions('user_admin_notices');
            remove_all_actions('admin_notices');
        });
    }

    /**
     * Add admin menus
     */
    public function add_admin_menu(): void
    {
        add_submenu_page(
            'study-planner',
            'Topics',
            'Topics',
            'manage_options',
            Settings::SLUG_TOPICS,
            array($this, 'load_view')
        );
        $url = Initializer::get_admin_url(Settings::SLUG_TOPICS);
        Initializer::add_to_localize('page_topics', $url);
    }

    public function load_view(): void
    {
        do_action('sp_enqueue_default_admin_topics');
        \StudyPlanner\load_template('admin/admin-topics');
    }

    public function get_page_data(): array
    {

        return [

        ];
    }

    public function localize_data(): void
    {
        Initializer::add_to_localize('deck_groups', $this->get_page_data());
    }

    public function register_scripts(): void
    {
//		$dis = $this;
//			$css = Initializer::$css_url . '/admin/admin-deck-groups.css';
//        $css = Initializer::$js_url . '/admin/admin-topics.css';
//		$js  = Initializer::$js_url . '/admin/admin-topics.js';
        $js = FileService::mp_get_js_url('admin-topics');
        $css = FileService::mp_get_css_url('admin-topics');
//		$js = "http://localhost:5173/src/admin/admin-topics.ts";

        wp_enqueue_editor();
        wp_register_style('sp-admin-topics', $css, [], Initializer::$script_version);
        wp_register_script('sp-admin-topics', $js, ['jquery'], Initializer::$script_version, true);

        // enqueue the scripts
        add_action('sp_enqueue_default_admin_topics', function () {
            do_action('sp_enqueue_default_admin_scripts');
            wp_enqueue_style('sp-admin-topics');
            wp_enqueue_script('sp-admin-topics');

            $this->localize_data();
        });
    }


}