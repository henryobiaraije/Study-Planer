<?php
/**
 *  Plugin. Common functions.
 *
 * @class   Common
 * @package StudyPlannerPro/Includes/Libs
 */

namespace StudyPlannerPro\Libs;

defined('ABSPATH') || exit;

/**
 * Common Processing class
 *
 * @author MACHINE PEREERE Contact: mpereere@gmail.com
 * Created on : Dec 26, 2019, 1:25:50 PM
 */
class Common {

    public static $hold_foot_script = [];
    public static $hold_footer_script = [];
    public static $online_now = false;
    public static $plugin_name = "";

    /** Date settings */
    public static $dates_according_to_ip = false;

    /**
     * Holds Default Plugin Directory
     *
     * @var string
     */
    public static $plugin_dir = '';
    public static $images_dir = '';
    public static $assets_dir = '';
    public static $fonts_dir = '';
    public static $fonts_url = '';
    public static $plugin_url = "";
    public static $nonce_key = "gase4g4g2f";

    public static $online_start_count = 1;
    /**  Development Or Production */
    public static $script_version = 1.0;
    public static $in_development = true;

    /* IMG */
    private static $instance = null;

    function __construct($dates_according_to_ip = false) {

        if ($_SERVER["SERVER_NAME"] === "localhost") {
            //      self::$SCRIPT_VERSION = time();
        } else {
            self::$online_now = true;
        }
        if (isset($_SERVER['SERVER_NAME'])) {
            if ($_SERVER["SERVER_NAME"] === "localhost") {

            } else {
                self::$online_start_count = 0;
            }
        }

        if (self::$online_now) {

        }

        //		Common::in_script( [
        //			'online now' => self::$ONLINE_NOW,
        //		] );
        $this->init_constants();
    }

    /**
     * GetContents Obstart & Loads file & Obclean
     *
     * @param  string  $filename  The file name.
     *
     * @return string file content
     */
    public static function get_contents($filename) {
        ob_start();
        require $filename;
        $code = ob_get_clean();
        //		$code = ob_get_contents();
        //		ob_end_clean();
        return $code;
    }

    public static function get_days_or_weeks_or_months($days_count) {
        $ret = '';
        if ($days_count < 2) {
            $ret = $days_count.' day';
        } elseif ($days_count < 8) {
            $ret = $days_count.' days';
        } elseif ($days_count < 28) {
            $ret = number_format(($days_count / 7)).' weeks';
        } elseif ($days_count > 27) {
            $ret = number_format(($days_count / 30), 2).' days';
        }
        return $ret;
    }

    public static function get_days_or_months($days_count) {
        $ret = '';
        if ($days_count < 2) {
            $ret = number_format($days_count).' day';
        } elseif ($days_count < 27) {
            $ret = number_format($days_count).' days';
        } elseif ($days_count > 27) {
            $ret = number_format(($days_count / 30), 2).' days';
        }
        return $ret;
    }

    public static function getDbArray($get, $look_for) {
        return isset($get[$look_for]) ? [$get] : $get;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Common();
        }

        return self::$instance;
    }


    public static function verify_post($post, $must_be_logged_in = false, $must_be_admin = false, $forget_nonce = false) {
        if ($must_be_admin) {
            if (!current_user_can('administrator')) {
                Common::send_error('Sorry, only admins can do this.', [
                    'post' => $post,
                ]);
            }
        }

        if (true === $must_be_logged_in) {
            if (!is_user_logged_in()) {
                Common::send_error('Sorry, you must be logged in first.', [
                    'post' => $post,
                ]);
            }
        }

        if (!(isset($post[Common::VAR_1]))) {
            Common::send_error('Unknown Request..', [
                'post' => $post,
            ]);
        }
        if (!$forget_nonce || $must_be_admin) {
            $nonce = $post[Common::VAR_1];
            if (!wp_verify_nonce($nonce, StudyPlannerPro::$nonce_key)) {
                Common::send_error('Session Expired. Please reload page.');
            }
        }


    }

    private function init_constants() {
        self::$plugin_name = 'book-cover-builder-pro';
        self::$assets_dir  = ABSPATH."wp-content/plugins/".self::$plugin_name.'/assets';
        self::$plugin_dir  = ABSPATH."wp-content/plugins/".self::$plugin_name;
        self::$images_dir  = get_site_url()."/wp-content/plugins/".self::$plugin_name.'/assets/images';
        self::$fonts_url   = get_site_url()."/wp-content/plugins/".self::$plugin_name.'/assets/fonts';
        self::$plugin_url  = get_site_url()."/wp-content/plugins/".self::$plugin_name;
        if (self::$in_development) {
            self::$script_version = time();
        }

        add_action('mp_wr_good', [$this, 'send_good'], 10, 2);
        add_action('mp_wr_bad', [$this, 'send_bad'], 10, 2);
    }

    public static function money_format($float, $decimal_place = 2, $decimal_point = '.') {
        $float = (float) $float;

        return number_format($float, $decimal_place, $decimal_point, ',');
    }

    public static function get_ajax_url() {
        return get_admin_url('wcmv-admin.js')."admin-ajax.php";;
    }


    public static function in_script(
        $data_to_assign,
        $var_name = null,
        $echo_in_footer = false
    ) {
        if ($var_name === null) {
            $var_name = "mppr_".rand(443, 4857839);
        }
        // var_dump($data_to_assign);
        $ff = "<script>";
        $ff .= " var ".$var_name." = ".wp_json_encode($data_to_assign)."; ";
        $ff .= " console.log(".$var_name."); ";
        $ff .= "</script>";
        if ($echo_in_footer === true) {
            self::$hold_footer_script = " ".$ff." ";
        } else {
            echo $ff;
        }
    }

    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings and check if `rest_route` starts with `/`
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     * @author matzeeable
     */
    public static function is_rest() {
        if (defined('REST_REQUEST') && REST_REQUEST // (#1)
            || isset($_GET['rest_route']) // (#2)
            && strpos($_GET['rest_route'], '/', 0) === 0) {
            return true;
        }

        // (#3)
        global $wp_rewrite;
        if ($wp_rewrite === null) {
            $wp_rewrite = new WP_Rewrite();
        }

        // (#4)
        $rest_url    = wp_parse_url(trailingslashit(rest_url()));
        $current_url = wp_parse_url(add_query_arg(array()));

        return strpos($current_url['path'], $rest_url['path'], 0) === 0;
    }

    public static function add_script($data_to_assign, $var_name) {
        if (wp_doing_ajax() || self::is_rest()) {
            return;
        }
        $ff = "<script>";
        $ff .= " var ".$var_name." = ".wp_json_encode($data_to_assign)."; ";
        $ff .= "</script>";
        echo $ff;

    }

    // Function to get the client IP address
    public static function get_ip_address() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            } else {
                if (getenv('HTTP_X_FORWARDED')) {
                    $ipaddress = getenv('HTTP_X_FORWARDED');
                } else {
                    if (getenv('HTTP_FORWARDED_FOR')) {
                        $ipaddress = getenv('HTTP_FORWARDED_FOR');
                    } else {
                        if (getenv('HTTP_FORWARDED')) {
                            $ipaddress = getenv('HTTP_FORWARDED');
                        } else {
                            if (getenv('REMOTE_ADDR')) {
                                $ipaddress = getenv('REMOTE_ADDR');
                            } else {
                                $ipaddress = 'UNKNOWN';
                            }
                        }
                    }
                }
            }
        }

        return $ipaddress;
    }

    public static function eko($data, $message = "") {
        self::getInstance()->send_bad($data, $message);
    }

    public static function post_already_exist($post_title, $post_type) {
        $exists_query = new \WP_Query([
            'name'          => $post_title,
            'post_per_page' => 1,
            'post_status'   => 'publish',
            'post_type'     => $post_type,
        ]);
        if (!empty($exists_query->get_posts())) {
            return true;
        }

        return false;
    }

    public static function post_already_exist_in_parent($post_title, $post_type, $parent_id) {
        $ret          = false;
        $exists_query = new \WP_Query([
            'name'          => $post_title,
            'post_per_page' => 1,
            'post_status'   => 'publish',
            'post_type'     => $post_type,
            //				'post_parent'   => $parent_id,
        ]);

        foreach ($exists_query->get_posts() as $post) {
            if ($post->post_parent == $parent_id) {
                $ret = true;
            }
        }

        //			Common::send_failure( [
        //				'post_already_exist_in_parent',
        //				'exist_query' => $exists_query,
        //				'$ret' => $ret,
        //				'by_name' => get_page_by_title($post_title,ARRAY_A,$post_type),
        //			] );
        return $ret;
    }

    public static function post_already_exist_in_parent2($post_title, $post_type, $parent_id) {
        $ret          = false;
        $exists_query = new \WP_Query([
            'name'          => $post_title,
            'post_per_page' => 1,
            'post_status'   => 'publish',
            'post_type'     => $post_type,
            //				'post_parent'   => $parent_id,
        ]);

        foreach ($exists_query->get_posts() as $post) {
            if ($post->post_parent == $parent_id) {
                $ret = true;
            }
        }

        Common::send_error([
            'post_already_exist_in_parent',
            'exist_query' => $exists_query,
            '$ret'        => $ret,
            'by_name'     => get_page_by_title($post_title, ARRAY_A, $post_type),
        ]);

        return $ret;
    }

    public static function post_already_exist_excluded($post_title, $post_type, $excluded_post_id) {
        $exists_query = new \WP_Query([
            'name'          => $post_title,
            'post_per_page' => 1,
            'post_status'   => 'publish',
            'post_type'     => $post_type,
            'post__not_in'  => [$excluded_post_id],
        ]);
        if (!empty($exists_query->get_posts())) {
            return true;
        }

        return false;
    }

    public static function in_script_no_var($statment) {
        $ff = "<script>";
        $ff .= $statment;
        $ff .= "</script>";
        echo $ff;
    }

    public static function in_script_footer($data_to_assign, string $var_name = null, $echo_in_footer = false) {
        if ($var_name === null) {
            $var_name = "mppr_".rand(443, 4857839);
        }
        // var_dump($data_to_assign);
        $ff = "<script>";
        $ff .= " var ".$var_name." = ".json_encode($data_to_assign)."; ";
        $ff .= " console.log(".$var_name."); ";
        $ff .= "</script>";
        // if ($echo_in_footer === TRUE) {
        self::$hold_footer_script[] = $ff;
        // } else {
        //   echo $ff;
        // }
    }

    public static function getPost() {
        $data = json_decode(trim(preg_replace('/\\\"/', "\"",
            $_POST["form_data"])), true);
        if ($data == null) {
            $data = json_decode(stripslashes($_POST["form_data"]), true);
        }

        //    self::sendFailure(['data' => $data]);
        return $data['data'];
    }

    public static function echo_footer_scripts() {
        // die(self::$HOLD_FOOTER_SCRIPT);
        foreach (self::$hold_footer_script as $value) {
            echo $value;
        }
    }

    public static function in_pre($a) {
        $b = ["<pre>", $a, "</pre>"];
        var_dump($b);

        return $b;
    }

    public static function get_page_url($title) {
        $page = get_page_by_title($title);
        $url  = "";
        if ($page) {
            $page_id = $page->ID;
            $url     = get_permalink($page_id);
            if (empty($url)) {
                $url = $page->guid;
            }
        }
        $url = trim($url);

        return $url;
    }

    public static function get_page_url_by_id($page_id) {

        $url = get_permalink($page_id);
        if (empty($url)) {
            $page = get_post($page_id);
            $url  = $page->guid;
        }

        $url = trim($url);

        return $url;
    }

    public static function send_success(
        $message,
        $data = [],
        $extra = [],
        $use = []
    ) {
        self::sendOutStatic(self::$S_F_SUCCESS, $data, $message, $extra, $use = []);
        //    add_action('mp_wr_good', [$this, 'send_good'], 10, 2);
    }

    public
    static function send_error(
        $message,
        $data = []
    ) {
        //    self::sendOut(self::$S_F_FAILURE, $string);
        //    add_action('mp_wr_bad', [$this, 'send_bad'], 10, 2);
        self::sendOutStatic(self::$S_F_FAILURE, $data, $message);
    }

    public
    static function delete_chile_pages(
        $parent_page_name
    ) {
        $parent_page = get_page_by_title($parent_page_name);
        $args        = array(
            'post_parent' => $parent_page->ID,
            'post_type'   => 'page',
        );
        $posts       = get_posts($args);
        //			Common::sendFailure( [
        //				'posts' => $posts,
        //			] );
        if (is_array($posts) && count($posts) > 0) {
            foreach ($posts as $po) {
                wp_delete_post($po->ID, true);
            }
        }
    }

    public
    static function delete_page(
        $page_name = '',
        $page_id = -1
    ) {
        if ($page_id > -1) {
            wp_delete_post($page_id, true);

            return;
        }
        $post = get_page_by_title($page_name);
        if ($post) {
            wp_delete_post($post->ID, true);
        }
    }

    /**
     * @param  string  $page_name
     * @param  string  $parent_page_name
     * @param  array  $meta  value pairs of meta and values
     */
    public
    static function create_page(
        $page_name,
        $parent_page_name = '',
        $meta_values = []
    ) {
        $new_page_id  = false;
        $post_details = [];

        if (strlen($parent_page_name) > 2) {
            $parent_page = get_page_by_title(Start::PAGE_HOTEL);
            if ($parent_page !== null) {
                $post_details['post_parent'] = $parent_page->ID;
            }
        }
        if (count($meta_values) > 0) {
            $post_details['meta_input'] = $meta_values;
        }

        $page = get_page_by_title($page_name);
        if (null === $page) {
            $post_details = [
                'post_title'   => $page_name,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_type'    => 'page',
            ];
            $new_page_id  = wp_insert_post($post_details);
        }

        return $new_page_id;
    }

    public static function sendSuccess($string, array $array) {
    }

    public
    function send_good(
        $data,
        $message
    ) {
        // var_dump(['start',$data]);
        $arr                     = [];
        $arr[self::$S_F_STATUS]  = self::$S_F_SUCCESS;
        $arr[self::$S_F_DATA]    = $data;
        $arr[self::$S_F_MESSAGE] = $message;
        echo(json_encode($arr));
        die;
    }

    public
    function send_bad(
        $data,
        $message
    ) {
        // var_dump(['start',$data]);
        $arr                     = [];
        $arr[self::$S_F_STATUS]  = self::$S_F_FAILURE;
        $arr[self::$S_F_DATA]    = $data;
        $arr[self::$S_F_MESSAGE] = $message;
        echo(json_encode($arr));
        die;
    }

    public
    static function sendOutStatic(
        $status,
        $data,
        $message,
        $extra = [],
        $use = []
    ) {
        // var_dump(['start',$data]);
        $arr                               = [];
        $arr[self::$S_F_STATUS]            = $status;
        $arr[self::$S_F_DATA]              = $data;
        $arr[self::$S_F_MESSAGE]           = $message;
        $arr['ghi30'.rand(20, 483).time()] = $extra;
        $arr['use_'.rand(20, 764).time()]  = $use;
        echo(json_encode($arr));
        die;
    }

    public
    function sendOut(
        $status,
        $data,
        $message
    ) {
        // var_dump(['start',$data]);
        $arr                     = [];
        $arr[self::$S_F_STATUS]  = $status;
        $arr[self::$S_F_DATA]    = $data;
        $arr[self::$S_F_MESSAGE] = $message;
        echo(json_encode($arr));
        die;
    }

    /**
     * @param  int  $days  e.g. -1 for yesterday or +1 for tomorrow
     *
     * @return false|string
     */
    public static function getDateTime($days = 0, $hours = 0) {

        $date = date('Y-m-d H:i:s');
        if ($days !== 0) {
            $days = "$days days";
            $date = date('Y-m-d H:i:s', strtotime($days));
        }
        if ($hours !== 0) {
            $date = date('Y-m-d H:i:s', strtotime($date) + 60 * 60);
        }

        return $date;

    }

    public static function format_datetime($date) {
        return date('Y-m-d H:i:s', strtotime($date));

    }

    /**
     * @param  int  $days
     * @param  string  $format
     *
     * @return false|string
     */
    public
    static function getDate(
        $days = 0,
        $format = 'Y-m-d'
    ) {
        $date = date($format);
        if ($days !== 0) {
            $days = "$days days";
            $date = date($format, strtotime($days));
        }

        return $date;
    }

    public
    static function getTime() {

        return date('H:i:s');
    }

    public
    static function getJunk(
        $deviceId = "nothing"
    ) {
        $time       = time();
        $time_split = str_split($time, 2);
        $loop       = rand(1, 5);
        list($_one, $_two, $_three, $_four, $_five) = $time_split;
        if ($loop == 1) {
            list($_four, $_one, $_two, $_three, $_five) = $time_split;
        } elseif ($loop == 2) {
            list($_two, $_one, $_three, $_four, $_five) = $time_split;
        } elseif ($loop == 3) {
            list($_two, $_three, $_one, $_four, $_five) = $time_split;
        } elseif ($loop == 4) {
            list($_two, $_three, $_four, $_one, $_five) = $time_split;
        } elseif ($loop == 5) {
            list($_one, $_two, $_four, $_three, $_five) = $time_split;
        }

        $time = time();
        $a    = rand(1, 9999);
        $b    = rand(77, 4623);
        $c    = md5(Common::getDateTime());
        $f    = sha1(md5(Common::getDateTime()));
        $g    = sha1(strtotime("now"));
        $e    = md5(sha1(rand(15, 3425)));
        $d    = sha1($deviceId);
        $dId  = sha1(md5(sha1($deviceId.rand(0, 8234))));
        $lg   = sha1(log(4567.08794038));
        $h    = md5(sha1(md5(sha1(rand(12345, 253) + rand(0,
                2211)))));
        $ret  = sha1(md5($a)).md5(sha1($b)).$_one.$c.$d.$_two.$e.$_three.$f.$g.$dId.$_four.$lg.$_five.$h;
        $key  = "Key";
        //    $rrrr = strip_tags(stripslashes(stripcslashes(rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $ret, MCRYPT_MODE_ECB))))));
        $rrrr = strip_tags(stripslashes(stripcslashes(rtrim(base64_encode($ret)))));
        $rrrr = str_replace("+", "", $rrrr);
        $rrrr = str_replace("/", "", $rrrr);
        //    var_dump($rrrr);
        $rrrr = str_shuffle($rrrr);

        return $rrrr;
    }

    public
    static function getJunkSmall(
        $length = 10
    ) {
        //    $a = rand(0.56, 789504);
        //    $b = md5(rand(4, 18) . sha1($id));
        //    $d = sha1(C::getDateTime());
        //    $ret = $a . $b . $d;
        //    return $ret;
        $id = "YieH";

        $junk = self::getJunk($id);
        $junk = str_replace('0', '', $junk);
        $junk = str_replace('O', '', $junk);
        $junk = str_replace('I', '', $junk);
        $junk = str_replace('1', '', $junk);
        $junk = str_replace('l', '', $junk);
        //    $sub = substr($junk, rand(0, 50), rand(40, 60));
        $sub = substr($junk, 0, $length);

        return $sub;
    }

    public
    static function is_valid_post_variables(
        $post,
        $number
    ) {
        $final    = false;
        $allValus = [
            self::VAR_0,
            self::VAR_1,
            self::VAR_2,
            self::VAR_3,
            self::VAR_4,
            self::VAR_5,
            self::VAR_6,
            self::VAR_7,
            self::VAR_8,
            self::VAR_9,
            self::VAR_10,
            self::VAR_11,
            self::VAR_12,
            self::VAR_13,
            self::VAR_14,
            self::VAR_15,
            self::VAR_16,
            self::VAR_17,
            self::VAR_18,
            self::VAR_19,
            self::VAR_20,
        ];

        if (is_array($post) && (count($post) === $number)) {
            $final = true;
            for ($a = 0; $a < $number; $a++) {
                if (!array_key_exists($allValus[$a], $post)) {
                    $final = false;
                }
            }
        }
        if ($final === false) {
            self::send_error("Invalid Request");
        }

        return $final;
        //   self::organizeQuery($allValus, $have);
    }

    public static function get_time_zones() {
        $timezones = array(
            'America/Adak'              => '(GMT-10:00) America/Adak (Hawaii-Aleutian Standard Time)',
            'America/Atka'              => '(GMT-10:00) America/Atka (Hawaii-Aleutian Standard Time)',
            'America/Anchorage'         => '(GMT-9:00) America/Anchorage (Alaska Standard Time)',
            'America/Juneau'            => '(GMT-9:00) America/Juneau (Alaska Standard Time)',
            'America/Nome'              => '(GMT-9:00) America/Nome (Alaska Standard Time)',
            'America/Yakutat'           => '(GMT-9:00) America/Yakutat (Alaska Standard Time)',
            'America/Dawson'            => '(GMT-8:00) America/Dawson (Pacific Standard Time)',
            'America/Ensenada'          => '(GMT-8:00) America/Ensenada (Pacific Standard Time)',
            'America/Los_Angeles'       => '(GMT-8:00) America/Los_Angeles (Pacific Standard Time)',
            'America/Tijuana'           => '(GMT-8:00) America/Tijuana (Pacific Standard Time)',
            'America/Vancouver'         => '(GMT-8:00) America/Vancouver (Pacific Standard Time)',
            'America/Whitehorse'        => '(GMT-8:00) America/Whitehorse (Pacific Standard Time)',
            'Canada/Pacific'            => '(GMT-8:00) Canada/Pacific (Pacific Standard Time)',
            'Canada/Yukon'              => '(GMT-8:00) Canada/Yukon (Pacific Standard Time)',
            'Mexico/BajaNorte'          => '(GMT-8:00) Mexico/BajaNorte (Pacific Standard Time)',
            'America/Boise'             => '(GMT-7:00) America/Boise (Mountain Standard Time)',
            'America/Cambridge_Bay'     => '(GMT-7:00) America/Cambridge_Bay (Mountain Standard Time)',
            'America/Chihuahua'         => '(GMT-7:00) America/Chihuahua (Mountain Standard Time)',
            'America/Dawson_Creek'      => '(GMT-7:00) America/Dawson_Creek (Mountain Standard Time)',
            'America/Denver'            => '(GMT-7:00) America/Denver (Mountain Standard Time)',
            'America/Edmonton'          => '(GMT-7:00) America/Edmonton (Mountain Standard Time)',
            'America/Hermosillo'        => '(GMT-7:00) America/Hermosillo (Mountain Standard Time)',
            'America/Inuvik'            => '(GMT-7:00) America/Inuvik (Mountain Standard Time)',
            'America/Mazatlan'          => '(GMT-7:00) America/Mazatlan (Mountain Standard Time)',
            'America/Phoenix'           => '(GMT-7:00) America/Phoenix (Mountain Standard Time)',
            'America/Shiprock'          => '(GMT-7:00) America/Shiprock (Mountain Standard Time)',
            'America/Yellowknife'       => '(GMT-7:00) America/Yellowknife (Mountain Standard Time)',
            'Canada/Mountain'           => '(GMT-7:00) Canada/Mountain (Mountain Standard Time)',
            'Mexico/BajaSur'            => '(GMT-7:00) Mexico/BajaSur (Mountain Standard Time)',
            'America/Belize'            => '(GMT-6:00) America/Belize (Central Standard Time)',
            'America/Cancun'            => '(GMT-6:00) America/Cancun (Central Standard Time)',
            'America/Chicago'           => '(GMT-6:00) America/Chicago (Central Standard Time)',
            'America/Costa_Rica'        => '(GMT-6:00) America/Costa_Rica (Central Standard Time)',
            'America/El_Salvador'       => '(GMT-6:00) America/El_Salvador (Central Standard Time)',
            'America/Guatemala'         => '(GMT-6:00) America/Guatemala (Central Standard Time)',
            'America/Knox_IN'           => '(GMT-6:00) America/Knox_IN (Central Standard Time)',
            'America/Managua'           => '(GMT-6:00) America/Managua (Central Standard Time)',
            'America/Menominee'         => '(GMT-6:00) America/Menominee (Central Standard Time)',
            'America/Merida'            => '(GMT-6:00) America/Merida (Central Standard Time)',
            'America/Mexico_City'       => '(GMT-6:00) America/Mexico_City (Central Standard Time)',
            'America/Monterrey'         => '(GMT-6:00) America/Monterrey (Central Standard Time)',
            'America/Rainy_River'       => '(GMT-6:00) America/Rainy_River (Central Standard Time)',
            'America/Rankin_Inlet'      => '(GMT-6:00) America/Rankin_Inlet (Central Standard Time)',
            'America/Regina'            => '(GMT-6:00) America/Regina (Central Standard Time)',
            'America/Swift_Current'     => '(GMT-6:00) America/Swift_Current (Central Standard Time)',
            'America/Tegucigalpa'       => '(GMT-6:00) America/Tegucigalpa (Central Standard Time)',
            'America/Winnipeg'          => '(GMT-6:00) America/Winnipeg (Central Standard Time)',
            'Canada/Central'            => '(GMT-6:00) Canada/Central (Central Standard Time)',
            'Canada/East-Saskatchewan'  => '(GMT-6:00) Canada/East-Saskatchewan (Central Standard Time)',
            'Canada/Saskatchewan'       => '(GMT-6:00) Canada/Saskatchewan (Central Standard Time)',
            'Chile/EasterIsland'        => '(GMT-6:00) Chile/EasterIsland (Easter Is. Time)',
            'Mexico/General'            => '(GMT-6:00) Mexico/General (Central Standard Time)',
            'America/Atikokan'          => '(GMT-5:00) America/Atikokan (Eastern Standard Time)',
            'America/Bogota'            => '(GMT-5:00) America/Bogota (Colombia Time)',
            'America/Cayman'            => '(GMT-5:00) America/Cayman (Eastern Standard Time)',
            'America/Coral_Harbour'     => '(GMT-5:00) America/Coral_Harbour (Eastern Standard Time)',
            'America/Detroit'           => '(GMT-5:00) America/Detroit (Eastern Standard Time)',
            'America/Fort_Wayne'        => '(GMT-5:00) America/Fort_Wayne (Eastern Standard Time)',
            'America/Grand_Turk'        => '(GMT-5:00) America/Grand_Turk (Eastern Standard Time)',
            'America/Guayaquil'         => '(GMT-5:00) America/Guayaquil (Ecuador Time)',
            'America/Havana'            => '(GMT-5:00) America/Havana (Cuba Standard Time)',
            'America/Indianapolis'      => '(GMT-5:00) America/Indianapolis (Eastern Standard Time)',
            'America/Iqaluit'           => '(GMT-5:00) America/Iqaluit (Eastern Standard Time)',
            'America/Jamaica'           => '(GMT-5:00) America/Jamaica (Eastern Standard Time)',
            'America/Lima'              => '(GMT-5:00) America/Lima (Peru Time)',
            'America/Louisville'        => '(GMT-5:00) America/Louisville (Eastern Standard Time)',
            'America/Montreal'          => '(GMT-5:00) America/Montreal (Eastern Standard Time)',
            'America/Nassau'            => '(GMT-5:00) America/Nassau (Eastern Standard Time)',
            'America/New_York'          => '(GMT-5:00) America/New_York (Eastern Standard Time)',
            'America/Nipigon'           => '(GMT-5:00) America/Nipigon (Eastern Standard Time)',
            'America/Panama'            => '(GMT-5:00) America/Panama (Eastern Standard Time)',
            'America/Pangnirtung'       => '(GMT-5:00) America/Pangnirtung (Eastern Standard Time)',
            'America/Port-au-Prince'    => '(GMT-5:00) America/Port-au-Prince (Eastern Standard Time)',
            'America/Resolute'          => '(GMT-5:00) America/Resolute (Eastern Standard Time)',
            'America/Thunder_Bay'       => '(GMT-5:00) America/Thunder_Bay (Eastern Standard Time)',
            'America/Toronto'           => '(GMT-5:00) America/Toronto (Eastern Standard Time)',
            'Canada/Eastern'            => '(GMT-5:00) Canada/Eastern (Eastern Standard Time)',
            'America/Caracas'           => '(GMT-4:-30) America/Caracas (Venezuela Time)',
            'America/Anguilla'          => '(GMT-4:00) America/Anguilla (Atlantic Standard Time)',
            'America/Antigua'           => '(GMT-4:00) America/Antigua (Atlantic Standard Time)',
            'America/Aruba'             => '(GMT-4:00) America/Aruba (Atlantic Standard Time)',
            'America/Asuncion'          => '(GMT-4:00) America/Asuncion (Paraguay Time)',
            'America/Barbados'          => '(GMT-4:00) America/Barbados (Atlantic Standard Time)',
            'America/Blanc-Sablon'      => '(GMT-4:00) America/Blanc-Sablon (Atlantic Standard Time)',
            'America/Boa_Vista'         => '(GMT-4:00) America/Boa_Vista (Amazon Time)',
            'America/Campo_Grande'      => '(GMT-4:00) America/Campo_Grande (Amazon Time)',
            'America/Cuiaba'            => '(GMT-4:00) America/Cuiaba (Amazon Time)',
            'America/Curacao'           => '(GMT-4:00) America/Curacao (Atlantic Standard Time)',
            'America/Dominica'          => '(GMT-4:00) America/Dominica (Atlantic Standard Time)',
            'America/Eirunepe'          => '(GMT-4:00) America/Eirunepe (Amazon Time)',
            'America/Glace_Bay'         => '(GMT-4:00) America/Glace_Bay (Atlantic Standard Time)',
            'America/Goose_Bay'         => '(GMT-4:00) America/Goose_Bay (Atlantic Standard Time)',
            'America/Grenada'           => '(GMT-4:00) America/Grenada (Atlantic Standard Time)',
            'America/Guadeloupe'        => '(GMT-4:00) America/Guadeloupe (Atlantic Standard Time)',
            'America/Guyana'            => '(GMT-4:00) America/Guyana (Guyana Time)',
            'America/Halifax'           => '(GMT-4:00) America/Halifax (Atlantic Standard Time)',
            'America/La_Paz'            => '(GMT-4:00) America/La_Paz (Bolivia Time)',
            'America/Manaus'            => '(GMT-4:00) America/Manaus (Amazon Time)',
            'America/Marigot'           => '(GMT-4:00) America/Marigot (Atlantic Standard Time)',
            'America/Martinique'        => '(GMT-4:00) America/Martinique (Atlantic Standard Time)',
            'America/Moncton'           => '(GMT-4:00) America/Moncton (Atlantic Standard Time)',
            'America/Montserrat'        => '(GMT-4:00) America/Montserrat (Atlantic Standard Time)',
            'America/Port_of_Spain'     => '(GMT-4:00) America/Port_of_Spain (Atlantic Standard Time)',
            'America/Porto_Acre'        => '(GMT-4:00) America/Porto_Acre (Amazon Time)',
            'America/Porto_Velho'       => '(GMT-4:00) America/Porto_Velho (Amazon Time)',
            'America/Puerto_Rico'       => '(GMT-4:00) America/Puerto_Rico (Atlantic Standard Time)',
            'America/Rio_Branco'        => '(GMT-4:00) America/Rio_Branco (Amazon Time)',
            'America/Santiago'          => '(GMT-4:00) America/Santiago (Chile Time)',
            'America/Santo_Domingo'     => '(GMT-4:00) America/Santo_Domingo (Atlantic Standard Time)',
            'America/St_Barthelemy'     => '(GMT-4:00) America/St_Barthelemy (Atlantic Standard Time)',
            'America/St_Kitts'          => '(GMT-4:00) America/St_Kitts (Atlantic Standard Time)',
            'America/St_Lucia'          => '(GMT-4:00) America/St_Lucia (Atlantic Standard Time)',
            'America/St_Thomas'         => '(GMT-4:00) America/St_Thomas (Atlantic Standard Time)',
            'America/St_Vincent'        => '(GMT-4:00) America/St_Vincent (Atlantic Standard Time)',
            'America/Thule'             => '(GMT-4:00) America/Thule (Atlantic Standard Time)',
            'America/Tortola'           => '(GMT-4:00) America/Tortola (Atlantic Standard Time)',
            'America/Virgin'            => '(GMT-4:00) America/Virgin (Atlantic Standard Time)',
            'Antarctica/Palmer'         => '(GMT-4:00) Antarctica/Palmer (Chile Time)',
            'Atlantic/Bermuda'          => '(GMT-4:00) Atlantic/Bermuda (Atlantic Standard Time)',
            'Atlantic/Stanley'          => '(GMT-4:00) Atlantic/Stanley (Falkland Is. Time)',
            'Brazil/Acre'               => '(GMT-4:00) Brazil/Acre (Amazon Time)',
            'Brazil/West'               => '(GMT-4:00) Brazil/West (Amazon Time)',
            'Canada/Atlantic'           => '(GMT-4:00) Canada/Atlantic (Atlantic Standard Time)',
            'Chile/Continental'         => '(GMT-4:00) Chile/Continental (Chile Time)',
            'America/St_Johns'          => '(GMT-3:-30) America/St_Johns (Newfoundland Standard Time)',
            'Canada/Newfoundland'       => '(GMT-3:-30) Canada/Newfoundland (Newfoundland Standard Time)',
            'America/Araguaina'         => '(GMT-3:00) America/Araguaina (Brasilia Time)',
            'America/Bahia'             => '(GMT-3:00) America/Bahia (Brasilia Time)',
            'America/Belem'             => '(GMT-3:00) America/Belem (Brasilia Time)',
            'America/Buenos_Aires'      => '(GMT-3:00) America/Buenos_Aires (Argentine Time)',
            'America/Catamarca'         => '(GMT-3:00) America/Catamarca (Argentine Time)',
            'America/Cayenne'           => '(GMT-3:00) America/Cayenne (French Guiana Time)',
            'America/Cordoba'           => '(GMT-3:00) America/Cordoba (Argentine Time)',
            'America/Fortaleza'         => '(GMT-3:00) America/Fortaleza (Brasilia Time)',
            'America/Godthab'           => '(GMT-3:00) America/Godthab (Western Greenland Time)',
            'America/Jujuy'             => '(GMT-3:00) America/Jujuy (Argentine Time)',
            'America/Maceio'            => '(GMT-3:00) America/Maceio (Brasilia Time)',
            'America/Mendoza'           => '(GMT-3:00) America/Mendoza (Argentine Time)',
            'America/Miquelon'          => '(GMT-3:00) America/Miquelon (Pierre & Miquelon Standard Time)',
            'America/Montevideo'        => '(GMT-3:00) America/Montevideo (Uruguay Time)',
            'America/Paramaribo'        => '(GMT-3:00) America/Paramaribo (Suriname Time)',
            'America/Recife'            => '(GMT-3:00) America/Recife (Brasilia Time)',
            'America/Rosario'           => '(GMT-3:00) America/Rosario (Argentine Time)',
            'America/Santarem'          => '(GMT-3:00) America/Santarem (Brasilia Time)',
            'America/Sao_Paulo'         => '(GMT-3:00) America/Sao_Paulo (Brasilia Time)',
            'Antarctica/Rothera'        => '(GMT-3:00) Antarctica/Rothera (Rothera Time)',
            'Brazil/East'               => '(GMT-3:00) Brazil/East (Brasilia Time)',
            'America/Noronha'           => '(GMT-2:00) America/Noronha (Fernando de Noronha Time)',
            'Atlantic/South_Georgia'    => '(GMT-2:00) Atlantic/South_Georgia (South Georgia Standard Time)',
            'Brazil/DeNoronha'          => '(GMT-2:00) Brazil/DeNoronha (Fernando de Noronha Time)',
            'America/Scoresbysund'      => '(GMT-1:00) America/Scoresbysund (Eastern Greenland Time)',
            'Atlantic/Azores'           => '(GMT-1:00) Atlantic/Azores (Azores Time)',
            'Atlantic/Cape_Verde'       => '(GMT-1:00) Atlantic/Cape_Verde (Cape Verde Time)',
            'Africa/Abidjan'            => '(GMT+0:00) Africa/Abidjan (Greenwich Mean Time)',
            'Africa/Accra'              => '(GMT+0:00) Africa/Accra (Ghana Mean Time)',
            'Africa/Bamako'             => '(GMT+0:00) Africa/Bamako (Greenwich Mean Time)',
            'Africa/Banjul'             => '(GMT+0:00) Africa/Banjul (Greenwich Mean Time)',
            'Africa/Bissau'             => '(GMT+0:00) Africa/Bissau (Greenwich Mean Time)',
            'Africa/Casablanca'         => '(GMT+0:00) Africa/Casablanca (Western European Time)',
            'Africa/Conakry'            => '(GMT+0:00) Africa/Conakry (Greenwich Mean Time)',
            'Africa/Dakar'              => '(GMT+0:00) Africa/Dakar (Greenwich Mean Time)',
            'Africa/El_Aaiun'           => '(GMT+0:00) Africa/El_Aaiun (Western European Time)',
            'Africa/Freetown'           => '(GMT+0:00) Africa/Freetown (Greenwich Mean Time)',
            'Africa/Lome'               => '(GMT+0:00) Africa/Lome (Greenwich Mean Time)',
            'Africa/Monrovia'           => '(GMT+0:00) Africa/Monrovia (Greenwich Mean Time)',
            'Africa/Nouakchott'         => '(GMT+0:00) Africa/Nouakchott (Greenwich Mean Time)',
            'Africa/Ouagadougou'        => '(GMT+0:00) Africa/Ouagadougou (Greenwich Mean Time)',
            'Africa/Sao_Tome'           => '(GMT+0:00) Africa/Sao_Tome (Greenwich Mean Time)',
            'Africa/Timbuktu'           => '(GMT+0:00) Africa/Timbuktu (Greenwich Mean Time)',
            'America/Danmarkshavn'      => '(GMT+0:00) America/Danmarkshavn (Greenwich Mean Time)',
            'Atlantic/Canary'           => '(GMT+0:00) Atlantic/Canary (Western European Time)',
            'Atlantic/Faeroe'           => '(GMT+0:00) Atlantic/Faeroe (Western European Time)',
            'Atlantic/Faroe'            => '(GMT+0:00) Atlantic/Faroe (Western European Time)',
            'Atlantic/Madeira'          => '(GMT+0:00) Atlantic/Madeira (Western European Time)',
            'Atlantic/Reykjavik'        => '(GMT+0:00) Atlantic/Reykjavik (Greenwich Mean Time)',
            'Atlantic/St_Helena'        => '(GMT+0:00) Atlantic/St_Helena (Greenwich Mean Time)',
            'Europe/Belfast'            => '(GMT+0:00) Europe/Belfast (Greenwich Mean Time)',
            'Europe/Dublin'             => '(GMT+0:00) Europe/Dublin (Greenwich Mean Time)',
            'Europe/Guernsey'           => '(GMT+0:00) Europe/Guernsey (Greenwich Mean Time)',
            'Europe/Isle_of_Man'        => '(GMT+0:00) Europe/Isle_of_Man (Greenwich Mean Time)',
            'Europe/Jersey'             => '(GMT+0:00) Europe/Jersey (Greenwich Mean Time)',
            'Europe/Lisbon'             => '(GMT+0:00) Europe/Lisbon (Western European Time)',
            'Europe/London'             => '(GMT+0:00) Europe/London (Greenwich Mean Time)',
            'Africa/Algiers'            => '(GMT+1:00) Africa/Algiers (Central European Time)',
            'Africa/Bangui'             => '(GMT+1:00) Africa/Bangui (Western African Time)',
            'Africa/Brazzaville'        => '(GMT+1:00) Africa/Brazzaville (Western African Time)',
            'Africa/Ceuta'              => '(GMT+1:00) Africa/Ceuta (Central European Time)',
            'Africa/Douala'             => '(GMT+1:00) Africa/Douala (Western African Time)',
            'Africa/Kinshasa'           => '(GMT+1:00) Africa/Kinshasa (Western African Time)',
            'Africa/Lagos'              => '(GMT+1:00) Africa/Lagos (Western African Time)',
            'Africa/Libreville'         => '(GMT+1:00) Africa/Libreville (Western African Time)',
            'Africa/Luanda'             => '(GMT+1:00) Africa/Luanda (Western African Time)',
            'Africa/Malabo'             => '(GMT+1:00) Africa/Malabo (Western African Time)',
            'Africa/Ndjamena'           => '(GMT+1:00) Africa/Ndjamena (Western African Time)',
            'Africa/Niamey'             => '(GMT+1:00) Africa/Niamey (Western African Time)',
            'Africa/Porto-Novo'         => '(GMT+1:00) Africa/Porto-Novo (Western African Time)',
            'Africa/Tunis'              => '(GMT+1:00) Africa/Tunis (Central European Time)',
            'Africa/Windhoek'           => '(GMT+1:00) Africa/Windhoek (Western African Time)',
            'Arctic/Longyearbyen'       => '(GMT+1:00) Arctic/Longyearbyen (Central European Time)',
            'Atlantic/Jan_Mayen'        => '(GMT+1:00) Atlantic/Jan_Mayen (Central European Time)',
            'Europe/Amsterdam'          => '(GMT+1:00) Europe/Amsterdam (Central European Time)',
            'Europe/Andorra'            => '(GMT+1:00) Europe/Andorra (Central European Time)',
            'Europe/Belgrade'           => '(GMT+1:00) Europe/Belgrade (Central European Time)',
            'Europe/Berlin'             => '(GMT+1:00) Europe/Berlin (Central European Time)',
            'Europe/Bratislava'         => '(GMT+1:00) Europe/Bratislava (Central European Time)',
            'Europe/Brussels'           => '(GMT+1:00) Europe/Brussels (Central European Time)',
            'Europe/Budapest'           => '(GMT+1:00) Europe/Budapest (Central European Time)',
            'Europe/Copenhagen'         => '(GMT+1:00) Europe/Copenhagen (Central European Time)',
            'Europe/Gibraltar'          => '(GMT+1:00) Europe/Gibraltar (Central European Time)',
            'Europe/Ljubljana'          => '(GMT+1:00) Europe/Ljubljana (Central European Time)',
            'Europe/Luxembourg'         => '(GMT+1:00) Europe/Luxembourg (Central European Time)',
            'Europe/Madrid'             => '(GMT+1:00) Europe/Madrid (Central European Time)',
            'Europe/Malta'              => '(GMT+1:00) Europe/Malta (Central European Time)',
            'Europe/Monaco'             => '(GMT+1:00) Europe/Monaco (Central European Time)',
            'Europe/Oslo'               => '(GMT+1:00) Europe/Oslo (Central European Time)',
            'Europe/Paris'              => '(GMT+1:00) Europe/Paris (Central European Time)',
            'Europe/Podgorica'          => '(GMT+1:00) Europe/Podgorica (Central European Time)',
            'Europe/Prague'             => '(GMT+1:00) Europe/Prague (Central European Time)',
            'Europe/Rome'               => '(GMT+1:00) Europe/Rome (Central European Time)',
            'Europe/San_Marino'         => '(GMT+1:00) Europe/San_Marino (Central European Time)',
            'Europe/Sarajevo'           => '(GMT+1:00) Europe/Sarajevo (Central European Time)',
            'Europe/Skopje'             => '(GMT+1:00) Europe/Skopje (Central European Time)',
            'Europe/Stockholm'          => '(GMT+1:00) Europe/Stockholm (Central European Time)',
            'Europe/Tirane'             => '(GMT+1:00) Europe/Tirane (Central European Time)',
            'Europe/Vaduz'              => '(GMT+1:00) Europe/Vaduz (Central European Time)',
            'Europe/Vatican'            => '(GMT+1:00) Europe/Vatican (Central European Time)',
            'Europe/Vienna'             => '(GMT+1:00) Europe/Vienna (Central European Time)',
            'Europe/Warsaw'             => '(GMT+1:00) Europe/Warsaw (Central European Time)',
            'Europe/Zagreb'             => '(GMT+1:00) Europe/Zagreb (Central European Time)',
            'Europe/Zurich'             => '(GMT+1:00) Europe/Zurich (Central European Time)',
            'Africa/Blantyre'           => '(GMT+2:00) Africa/Blantyre (Central African Time)',
            'Africa/Bujumbura'          => '(GMT+2:00) Africa/Bujumbura (Central African Time)',
            'Africa/Cairo'              => '(GMT+2:00) Africa/Cairo (Eastern European Time)',
            'Africa/Gaborone'           => '(GMT+2:00) Africa/Gaborone (Central African Time)',
            'Africa/Harare'             => '(GMT+2:00) Africa/Harare (Central African Time)',
            'Africa/Johannesburg'       => '(GMT+2:00) Africa/Johannesburg (South Africa Standard Time)',
            'Africa/Kigali'             => '(GMT+2:00) Africa/Kigali (Central African Time)',
            'Africa/Lubumbashi'         => '(GMT+2:00) Africa/Lubumbashi (Central African Time)',
            'Africa/Lusaka'             => '(GMT+2:00) Africa/Lusaka (Central African Time)',
            'Africa/Maputo'             => '(GMT+2:00) Africa/Maputo (Central African Time)',
            'Africa/Maseru'             => '(GMT+2:00) Africa/Maseru (South Africa Standard Time)',
            'Africa/Mbabane'            => '(GMT+2:00) Africa/Mbabane (South Africa Standard Time)',
            'Africa/Tripoli'            => '(GMT+2:00) Africa/Tripoli (Eastern European Time)',
            'Asia/Amman'                => '(GMT+2:00) Asia/Amman (Eastern European Time)',
            'Asia/Beirut'               => '(GMT+2:00) Asia/Beirut (Eastern European Time)',
            'Asia/Damascus'             => '(GMT+2:00) Asia/Damascus (Eastern European Time)',
            'Asia/Gaza'                 => '(GMT+2:00) Asia/Gaza (Eastern European Time)',
            'Asia/Istanbul'             => '(GMT+2:00) Asia/Istanbul (Eastern European Time)',
            'Asia/Jerusalem'            => '(GMT+2:00) Asia/Jerusalem (Israel Standard Time)',
            'Asia/Nicosia'              => '(GMT+2:00) Asia/Nicosia (Eastern European Time)',
            'Asia/Tel_Aviv'             => '(GMT+2:00) Asia/Tel_Aviv (Israel Standard Time)',
            'Europe/Athens'             => '(GMT+2:00) Europe/Athens (Eastern European Time)',
            'Europe/Bucharest'          => '(GMT+2:00) Europe/Bucharest (Eastern European Time)',
            'Europe/Chisinau'           => '(GMT+2:00) Europe/Chisinau (Eastern European Time)',
            'Europe/Helsinki'           => '(GMT+2:00) Europe/Helsinki (Eastern European Time)',
            'Europe/Istanbul'           => '(GMT+2:00) Europe/Istanbul (Eastern European Time)',
            'Europe/Kaliningrad'        => '(GMT+2:00) Europe/Kaliningrad (Eastern European Time)',
            'Europe/Kiev'               => '(GMT+2:00) Europe/Kiev (Eastern European Time)',
            'Europe/Mariehamn'          => '(GMT+2:00) Europe/Mariehamn (Eastern European Time)',
            'Europe/Minsk'              => '(GMT+2:00) Europe/Minsk (Eastern European Time)',
            'Europe/Nicosia'            => '(GMT+2:00) Europe/Nicosia (Eastern European Time)',
            'Europe/Riga'               => '(GMT+2:00) Europe/Riga (Eastern European Time)',
            'Europe/Simferopol'         => '(GMT+2:00) Europe/Simferopol (Eastern European Time)',
            'Europe/Sofia'              => '(GMT+2:00) Europe/Sofia (Eastern European Time)',
            'Europe/Tallinn'            => '(GMT+2:00) Europe/Tallinn (Eastern European Time)',
            'Europe/Tiraspol'           => '(GMT+2:00) Europe/Tiraspol (Eastern European Time)',
            'Europe/Uzhgorod'           => '(GMT+2:00) Europe/Uzhgorod (Eastern European Time)',
            'Europe/Vilnius'            => '(GMT+2:00) Europe/Vilnius (Eastern European Time)',
            'Europe/Zaporozhye'         => '(GMT+2:00) Europe/Zaporozhye (Eastern European Time)',
            'Africa/Addis_Ababa'        => '(GMT+3:00) Africa/Addis_Ababa (Eastern African Time)',
            'Africa/Asmara'             => '(GMT+3:00) Africa/Asmara (Eastern African Time)',
            'Africa/Asmera'             => '(GMT+3:00) Africa/Asmera (Eastern African Time)',
            'Africa/Dar_es_Salaam'      => '(GMT+3:00) Africa/Dar_es_Salaam (Eastern African Time)',
            'Africa/Djibouti'           => '(GMT+3:00) Africa/Djibouti (Eastern African Time)',
            'Africa/Kampala'            => '(GMT+3:00) Africa/Kampala (Eastern African Time)',
            'Africa/Khartoum'           => '(GMT+3:00) Africa/Khartoum (Eastern African Time)',
            'Africa/Mogadishu'          => '(GMT+3:00) Africa/Mogadishu (Eastern African Time)',
            'Africa/Nairobi'            => '(GMT+3:00) Africa/Nairobi (Eastern African Time)',
            'Antarctica/Syowa'          => '(GMT+3:00) Antarctica/Syowa (Syowa Time)',
            'Asia/Aden'                 => '(GMT+3:00) Asia/Aden (Arabia Standard Time)',
            'Asia/Baghdad'              => '(GMT+3:00) Asia/Baghdad (Arabia Standard Time)',
            'Asia/Bahrain'              => '(GMT+3:00) Asia/Bahrain (Arabia Standard Time)',
            'Asia/Kuwait'               => '(GMT+3:00) Asia/Kuwait (Arabia Standard Time)',
            'Asia/Qatar'                => '(GMT+3:00) Asia/Qatar (Arabia Standard Time)',
            'Europe/Moscow'             => '(GMT+3:00) Europe/Moscow (Moscow Standard Time)',
            'Europe/Volgograd'          => '(GMT+3:00) Europe/Volgograd (Volgograd Time)',
            'Indian/Antananarivo'       => '(GMT+3:00) Indian/Antananarivo (Eastern African Time)',
            'Indian/Comoro'             => '(GMT+3:00) Indian/Comoro (Eastern African Time)',
            'Indian/Mayotte'            => '(GMT+3:00) Indian/Mayotte (Eastern African Time)',
            'Asia/Tehran'               => '(GMT+3:30) Asia/Tehran (Iran Standard Time)',
            'Asia/Baku'                 => '(GMT+4:00) Asia/Baku (Azerbaijan Time)',
            'Asia/Dubai'                => '(GMT+4:00) Asia/Dubai (Gulf Standard Time)',
            'Asia/Muscat'               => '(GMT+4:00) Asia/Muscat (Gulf Standard Time)',
            'Asia/Tbilisi'              => '(GMT+4:00) Asia/Tbilisi (Georgia Time)',
            'Asia/Yerevan'              => '(GMT+4:00) Asia/Yerevan (Armenia Time)',
            'Europe/Samara'             => '(GMT+4:00) Europe/Samara (Samara Time)',
            'Indian/Mahe'               => '(GMT+4:00) Indian/Mahe (Seychelles Time)',
            'Indian/Mauritius'          => '(GMT+4:00) Indian/Mauritius (Mauritius Time)',
            'Indian/Reunion'            => '(GMT+4:00) Indian/Reunion (Reunion Time)',
            'Asia/Kabul'                => '(GMT+4:30) Asia/Kabul (Afghanistan Time)',
            'Asia/Aqtau'                => '(GMT+5:00) Asia/Aqtau (Aqtau Time)',
            'Asia/Aqtobe'               => '(GMT+5:00) Asia/Aqtobe (Aqtobe Time)',
            'Asia/Ashgabat'             => '(GMT+5:00) Asia/Ashgabat (Turkmenistan Time)',
            'Asia/Ashkhabad'            => '(GMT+5:00) Asia/Ashkhabad (Turkmenistan Time)',
            'Asia/Dushanbe'             => '(GMT+5:00) Asia/Dushanbe (Tajikistan Time)',
            'Asia/Karachi'              => '(GMT+5:00) Asia/Karachi (Pakistan Time)',
            'Asia/Oral'                 => '(GMT+5:00) Asia/Oral (Oral Time)',
            'Asia/Samarkand'            => '(GMT+5:00) Asia/Samarkand (Uzbekistan Time)',
            'Asia/Tashkent'             => '(GMT+5:00) Asia/Tashkent (Uzbekistan Time)',
            'Asia/Yekaterinburg'        => '(GMT+5:00) Asia/Yekaterinburg (Yekaterinburg Time)',
            'Indian/Kerguelen'          => '(GMT+5:00) Indian/Kerguelen (French Southern & Antarctic Lands Time)',
            'Indian/Maldives'           => '(GMT+5:00) Indian/Maldives (Maldives Time)',
            'Asia/Calcutta'             => '(GMT+5:30) Asia/Calcutta (India Standard Time)',
            'Asia/Colombo'              => '(GMT+5:30) Asia/Colombo (India Standard Time)',
            'Asia/Kolkata'              => '(GMT+5:30) Asia/Kolkata (India Standard Time)',
            'Asia/Katmandu'             => '(GMT+5:45) Asia/Katmandu (Nepal Time)',
            'Antarctica/Mawson'         => '(GMT+6:00) Antarctica/Mawson (Mawson Time)',
            'Antarctica/Vostok'         => '(GMT+6:00) Antarctica/Vostok (Vostok Time)',
            'Asia/Almaty'               => '(GMT+6:00) Asia/Almaty (Alma-Ata Time)',
            'Asia/Bishkek'              => '(GMT+6:00) Asia/Bishkek (Kirgizstan Time)',
            'Asia/Dacca'                => '(GMT+6:00) Asia/Dacca (Bangladesh Time)',
            'Asia/Dhaka'                => '(GMT+6:00) Asia/Dhaka (Bangladesh Time)',
            'Asia/Novosibirsk'          => '(GMT+6:00) Asia/Novosibirsk (Novosibirsk Time)',
            'Asia/Omsk'                 => '(GMT+6:00) Asia/Omsk (Omsk Time)',
            'Asia/Qyzylorda'            => '(GMT+6:00) Asia/Qyzylorda (Qyzylorda Time)',
            'Asia/Thimbu'               => '(GMT+6:00) Asia/Thimbu (Bhutan Time)',
            'Asia/Thimphu'              => '(GMT+6:00) Asia/Thimphu (Bhutan Time)',
            'Indian/Chagos'             => '(GMT+6:00) Indian/Chagos (Indian Ocean Territory Time)',
            'Asia/Rangoon'              => '(GMT+6:30) Asia/Rangoon (Myanmar Time)',
            'Indian/Cocos'              => '(GMT+6:30) Indian/Cocos (Cocos Islands Time)',
            'Antarctica/Davis'          => '(GMT+7:00) Antarctica/Davis (Davis Time)',
            'Asia/Bangkok'              => '(GMT+7:00) Asia/Bangkok (Indochina Time)',
            'Asia/Ho_Chi_Minh'          => '(GMT+7:00) Asia/Ho_Chi_Minh (Indochina Time)',
            'Asia/Hovd'                 => '(GMT+7:00) Asia/Hovd (Hovd Time)',
            'Asia/Jakarta'              => '(GMT+7:00) Asia/Jakarta (West Indonesia Time)',
            'Asia/Krasnoyarsk'          => '(GMT+7:00) Asia/Krasnoyarsk (Krasnoyarsk Time)',
            'Asia/Phnom_Penh'           => '(GMT+7:00) Asia/Phnom_Penh (Indochina Time)',
            'Asia/Pontianak'            => '(GMT+7:00) Asia/Pontianak (West Indonesia Time)',
            'Asia/Saigon'               => '(GMT+7:00) Asia/Saigon (Indochina Time)',
            'Asia/Vientiane'            => '(GMT+7:00) Asia/Vientiane (Indochina Time)',
            'Indian/Christmas'          => '(GMT+7:00) Indian/Christmas (Christmas Island Time)',
            'Antarctica/Casey'          => '(GMT+8:00) Antarctica/Casey (Western Standard Time (Australia))',
            'Asia/Brunei'               => '(GMT+8:00) Asia/Brunei (Brunei Time)',
            'Asia/Choibalsan'           => '(GMT+8:00) Asia/Choibalsan (Choibalsan Time)',
            'Asia/Chongqing'            => '(GMT+8:00) Asia/Chongqing (China Standard Time)',
            'Asia/Chungking'            => '(GMT+8:00) Asia/Chungking (China Standard Time)',
            'Asia/Harbin'               => '(GMT+8:00) Asia/Harbin (China Standard Time)',
            'Asia/Hong_Kong'            => '(GMT+8:00) Asia/Hong_Kong (Hong Kong Time)',
            'Asia/Irkutsk'              => '(GMT+8:00) Asia/Irkutsk (Irkutsk Time)',
            'Asia/Kashgar'              => '(GMT+8:00) Asia/Kashgar (China Standard Time)',
            'Asia/Kuala_Lumpur'         => '(GMT+8:00) Asia/Kuala_Lumpur (Malaysia Time)',
            'Asia/Kuching'              => '(GMT+8:00) Asia/Kuching (Malaysia Time)',
            'Asia/Macao'                => '(GMT+8:00) Asia/Macao (China Standard Time)',
            'Asia/Macau'                => '(GMT+8:00) Asia/Macau (China Standard Time)',
            'Asia/Makassar'             => '(GMT+8:00) Asia/Makassar (Central Indonesia Time)',
            'Asia/Manila'               => '(GMT+8:00) Asia/Manila (Philippines Time)',
            'Asia/Shanghai'             => '(GMT+8:00) Asia/Shanghai (China Standard Time)',
            'Asia/Singapore'            => '(GMT+8:00) Asia/Singapore (Singapore Time)',
            'Asia/Taipei'               => '(GMT+8:00) Asia/Taipei (China Standard Time)',
            'Asia/Ujung_Pandang'        => '(GMT+8:00) Asia/Ujung_Pandang (Central Indonesia Time)',
            'Asia/Ulaanbaatar'          => '(GMT+8:00) Asia/Ulaanbaatar (Ulaanbaatar Time)',
            'Asia/Ulan_Bator'           => '(GMT+8:00) Asia/Ulan_Bator (Ulaanbaatar Time)',
            'Asia/Urumqi'               => '(GMT+8:00) Asia/Urumqi (China Standard Time)',
            'Australia/Perth'           => '(GMT+8:00) Australia/Perth (Western Standard Time (Australia))',
            'Australia/West'            => '(GMT+8:00) Australia/West (Western Standard Time (Australia))',
            'Australia/Eucla'           => '(GMT+8:45) Australia/Eucla (Central Western Standard Time (Australia))',
            'Asia/Dili'                 => '(GMT+9:00) Asia/Dili (Timor-Leste Time)',
            'Asia/Jayapura'             => '(GMT+9:00) Asia/Jayapura (East Indonesia Time)',
            'Asia/Pyongyang'            => '(GMT+9:00) Asia/Pyongyang (Korea Standard Time)',
            'Asia/Seoul'                => '(GMT+9:00) Asia/Seoul (Korea Standard Time)',
            'Asia/Tokyo'                => '(GMT+9:00) Asia/Tokyo (Japan Standard Time)',
            'Asia/Yakutsk'              => '(GMT+9:00) Asia/Yakutsk (Yakutsk Time)',
            'Australia/Adelaide'        => '(GMT+9:30) Australia/Adelaide (Central Standard Time (South Australia))',
            'Australia/Broken_Hill'     => '(GMT+9:30) Australia/Broken_Hill (Central Standard Time (South Australia/New South Wales))',
            'Australia/Darwin'          => '(GMT+9:30) Australia/Darwin (Central Standard Time (Northern Territory))',
            'Australia/North'           => '(GMT+9:30) Australia/North (Central Standard Time (Northern Territory))',
            'Australia/South'           => '(GMT+9:30) Australia/South (Central Standard Time (South Australia))',
            'Australia/Yancowinna'      => '(GMT+9:30) Australia/Yancowinna (Central Standard Time (South Australia/New South Wales))',
            'Antarctica/DumontDUrville' => '(GMT+10:00) Antarctica/DumontDUrville (Dumont-d\'Urville Time)',
            'Asia/Sakhalin'             => '(GMT+10:00) Asia/Sakhalin (Sakhalin Time)',
            'Asia/Vladivostok'          => '(GMT+10:00) Asia/Vladivostok (Vladivostok Time)',
            'Australia/ACT'             => '(GMT+10:00) Australia/ACT (Eastern Standard Time (New South Wales))',
            'Australia/Brisbane'        => '(GMT+10:00) Australia/Brisbane (Eastern Standard Time (Queensland))',
            'Australia/Canberra'        => '(GMT+10:00) Australia/Canberra (Eastern Standard Time (New South Wales))',
            'Australia/Currie'          => '(GMT+10:00) Australia/Currie (Eastern Standard Time (New South Wales))',
            'Australia/Hobart'          => '(GMT+10:00) Australia/Hobart (Eastern Standard Time (Tasmania))',
            'Australia/Lindeman'        => '(GMT+10:00) Australia/Lindeman (Eastern Standard Time (Queensland))',
            'Australia/Melbourne'       => '(GMT+10:00) Australia/Melbourne (Eastern Standard Time (Victoria))',
            'Australia/NSW'             => '(GMT+10:00) Australia/NSW (Eastern Standard Time (New South Wales))',
            'Australia/Queensland'      => '(GMT+10:00) Australia/Queensland (Eastern Standard Time (Queensland))',
            'Australia/Sydney'          => '(GMT+10:00) Australia/Sydney (Eastern Standard Time (New South Wales))',
            'Australia/Tasmania'        => '(GMT+10:00) Australia/Tasmania (Eastern Standard Time (Tasmania))',
            'Australia/Victoria'        => '(GMT+10:00) Australia/Victoria (Eastern Standard Time (Victoria))',
            'Australia/LHI'             => '(GMT+10:30) Australia/LHI (Lord Howe Standard Time)',
            'Australia/Lord_Howe'       => '(GMT+10:30) Australia/Lord_Howe (Lord Howe Standard Time)',
            'Asia/Magadan'              => '(GMT+11:00) Asia/Magadan (Magadan Time)',
            'Antarctica/McMurdo'        => '(GMT+12:00) Antarctica/McMurdo (New Zealand Standard Time)',
            'Antarctica/South_Pole'     => '(GMT+12:00) Antarctica/South_Pole (New Zealand Standard Time)',
            'Asia/Anadyr'               => '(GMT+12:00) Asia/Anadyr (Anadyr Time)',
            'Asia/Kamchatka'            => '(GMT+12:00) Asia/Kamchatka (Petropavlovsk-Kamchatski Time)',
        );
        return $timezones;
    }

    //  private $db_mid = "djijoepwijpihhph;";

    /**  */
    const VAR_0 = "var_0";

    const VAR_1 = "var_1";

    const VAR_2 = "var_2";

    const VAR_3 = "var_3";

    const VAR_4 = "var_4";

    const VAR_5 = "var_5";

    const VAR_6 = "var_6";

    const VAR_7 = "var_7";

    const VAR_8 = "var_8";

    const VAR_9 = "var_9";

    const VAR_10 = "var_10";

    const VAR_11 = "var_11";

    const VAR_12 = "var_12";

    const VAR_13 = "var_13";

    const VAR_14 = "var_14";

    const VAR_15 = "var_15";

    const VAR_16 = "var_16";

    const VAR_17 = "var_17";

    const VAR_18 = "var_18";

    const VAR_19 = "var_19";

    const VAR_20 = "var_20";

    /* success failure params */

    static $S_F_WHAT = "what";
    static $S_F_DATA = "data";
    static $S_F_MESSAGE = "message";
    static $S_F_STATUS = "status";
    static $S_F_SUCCESS = "0";
    static $S_F_FAILURE = "1";
    static $S_F_ERROR = "2";

}
