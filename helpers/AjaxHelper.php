<?php


namespace StudyPlanner\Helpers;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Model\Answered;
use Model\Card;
use Model\CardGroup;
use Model\CardGroups;
use Model\Deck;
use Model\DeckGroup;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use StudyPlanner\Initializer;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Tag;
use function StudyPlanner\get_default_image_display_type;
use function StudyPlanner\get_mature_card_days;
use function StudyPlanner\get_uncategorized_deck_group_id;
use function StudyPlanner\get_uncategorized_deck_id;

class AjaxHelper {
    /**
     * @var self $instance
     */
    private static $instance;

    private function __construct() {
    }

    public static function get_instance(): self {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new self();
        self::$instance->init_ajax();

        return self::$instance;
    }

    private function init_ajax() {
        // <editor-fold desc="Deck Group">
        add_action('admin_sp_ajax_admin_create_new_deck_group', array($this, 'ajax_admin_create_new_deck_group'));
        add_action('admin_sp_ajax_admin_update_deck_group', array($this, 'ajax_admin_update_deck_group'));
        add_action('admin_sp_ajax_admin_load_deck_group', array($this, 'ajax_admin_load_deck_group'));
        add_action('admin_sp_ajax_admin_search_deck_group', array($this, 'ajax_admin_search_deck_group'));
        add_action('admin_sp_ajax_admin_delete_deck_group', array($this, 'ajax_admin_delete_deck_group'));
        add_action('admin_sp_ajax_admin_trash_deck_group', array($this, 'ajax_admin_trash_deck_group'));
        // </editor-fold desc="Deck Group">
        // <editor-fold desc="Deck">
        add_action('admin_sp_ajax_admin_load_decks', array($this, 'ajax_admin_load_decks'));
        add_action('admin_sp_ajax_admin_search_decks', array($this, 'ajax_admin_search_decks'));
        add_action('admin_sp_ajax_admin_create_new_deck', array($this, 'ajax_admin_create_new_deck'));
        add_action('admin_sp_ajax_admin_update_decks', array($this, 'ajax_admin_update_decks'));
        add_action('admin_sp_ajax_admin_trash_decks', array($this, 'ajax_admin_trash_decks'));
        add_action('admin_sp_ajax_admin_delete_decks', array($this, 'ajax_admin_delete_decks'));
        // </editor-fold desc="Deck">
        // <editor-fold desc="Tag">
        add_action('admin_sp_ajax_admin_create_tag', array($this, 'ajax_admin_create_tag'));
        add_action('admin_sp_ajax_admin_load_tags', array($this, 'ajax_admin_load_tags'));
        add_action('admin_sp_ajax_admin_search_tags', array($this, 'ajax_admin_search_tags'));
        add_action('admin_sp_ajax_admin_trash_tags', array($this, 'ajax_admin_trash_tags'));
        add_action('admin_sp_ajax_admin_delete_tags', array($this, 'ajax_admin_delete_tags'));
        // </editor-fold desc="Tag">
        // <editor-fold desc="Others">
        add_action('admin_sp_ajax_admin_create_new_basic_card', array($this, 'ajax_admin_create_new_basic_card'));
        add_action('admin_sp_ajax_admin_load_image_attachment', array($this, 'ajax_admin_load_image_attachment'));
        add_action('admin_sp_ajax_admin_load_basic_card', array($this, 'ajax_admin_load_basic_card'));
        add_action('admin_sp_ajax_admin_trash_cards', array($this, 'ajax_admin_trash_cards'));
        add_action('admin_sp_ajax_admin_restore_card_group', array($this, 'ajax_admin_restore_card_group'));
        add_action('admin_sp_ajax_admin_delete_card_group', array($this, 'ajax_admin_delete_card_group'));
        add_action('admin_sp_ajax_admin_update_basic_card', array($this, 'admin_update_basic_card'));
        add_action('admin_sp_ajax_admin_load_cards_groups', array($this, 'ajax_admin_load_cards_groups'));
        add_action('admin_sp_ajax_admin_create_new_gap_card', array($this, 'ajax_admin_create_new_gap_card'));
        add_action('admin_sp_ajax_admin_update_gap_card', array($this, 'ajax_admin_update_gap_card'));
        add_action('admin_sp_ajax_admin_update_table_card', array($this, 'ajax_admin_update_table_card'));
        add_action('admin_sp_ajax_admin_update_image_card', array($this, 'ajax_admin_update_image_card'));
        add_action('admin_sp_ajax_admin_create_new_table_card', array($this, 'ajax_admin_create_new_table_card'));
        add_action('admin_sp_ajax_admin_create_new_image_card', array($this, 'ajax_admin_create_new_image_card'));
        add_action('admin_sp_ajax_admin_load_settings', array($this, 'ajax_admin_load_settings'));
        add_action('admin_sp_ajax_admin_update_settings', array($this, 'ajax_admin_update_settings'));
        // </editor-fold desc="Card">
    }

    // <editor-fold desc="Image Cards">

    public function ajax_admin_update_settings($post): void {
        //        Common::send_error([
        //            'ajax_admin_update_settings',
        //            'post' => $post,
        //        ]);

        $all              = $post[Common::VAR_2];
        $settings         = $all['settings'];
        $mature_card_days = (int) sanitize_text_field($settings['mature_card_days']);
        //        Common::send_error([
        //            'ajax_admin_update_settings',
        //            'post' => $post,
        //            '$mature_card_days' => $mature_card_days,
        //        ]);
        update_option(Settings::OPTION_MATURED_CARD_DAYS, $mature_card_days);

        Common::send_success('Saved successfully.');

    }

    public function ajax_admin_load_settings($post): void {
        //        Common::send_error([
        //            'ajax_admin_load_settings',
        //            'post' => $post,
        //        ]);

        $all = $post[Common::VAR_2];

        $mature_card_days = get_mature_card_days();

        $settings = [
            'mature_card_days' => $mature_card_days,
        ];

        Common::send_success('Settings loaded.', $settings);

    }

    public function ajax_admin_create_new_image_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_table_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_cards             = $all['cards'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $whole_question      = $e_card_group['whole_question'];
        $whole_question      = wp_json_encode($whole_question);
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $image_type          = $e_card_group['image_type'];
        $e_tags              = $e_card_group['tags'];
        $cg_name             = sanitize_text_field($e_card_group['name']);
        if (!in_array($image_type, get_default_image_display_type())) {
            Common::send_error('Please select a valid image display type');
        }
        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($whole_question)) {
            //				Common::send_error( 'Please provide a question' );
        }
        if (empty($e_cards)) {
            Common::send_error('No cards will be created');
        }
        if (empty($e_tags)) {
            Common::send_error('No tag selected');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }

        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$reverse'             => $reverse,
        //				'$whole_question'      => $whole_question,
        //				'$e_cards'             => $e_cards,
        //				'$e_card_group'        => $e_card_group,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$image_type'             => $image_type,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        Manager::beginTransaction();
        $card_group                 = new CardGroup();
        $card_group->whole_question = $whole_question;
        $card_group->card_type      = 'image';
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->image_type     = $image_type;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = $reverse;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        foreach ($e_cards as $one_card) {
            $question            = wp_json_encode($one_card['question']);
            $answer              = wp_json_encode($one_card['answer']);
            $hash                = $one_card['hash'];
            $c_number            = $one_card['c_number'];
            $card                = new Card();
            $card->question      = $question;
            $card->hash          = $hash;
            $card->answer        = $answer;
            $card->c_number      = $c_number;
            $card->card_group_id = $card_group->id;
            $card->save();
            //				Common::send_error( [
            //					'ajax_admin_create_new_basic_card',
            //					'post'                 => $post,
            //					'$one_card'            => $one_card,
            //					'toSql'                => $card_group->toSql(),
            //					'$reverse'             => $reverse,
            //					'$hash'                => $hash,
            //					'$question'            => $question,
            //					'$e_card_group'        => $e_card_group,
            //					'$whole_question'      => $whole_question,
            //					'$e_set_bg_as_default' => $e_set_bg_as_default,
            //					'$bg_image_id'         => $bg_image_id,
            //					'$answer'              => $answer,
            //					'$deck'                => $deck,
            //					'$cg_name'             => $cg_name,
            //					'$e_tags'              => $e_tags,
            //					'$schedule_at'         => $schedule_at,
            //				] );

        }

        Manager::commit();

        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }
        // Create card group


        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$e_cards'              => $e_cards,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        $edit_page = Initializer::get_admin_url(Settings::SLUG_IMAGE_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Created successfully.', $edit_page);

    }

    public function ajax_admin_update_image_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_update_table_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_cards             = $all['cards'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $whole_question      = wp_json_encode($e_card_group['whole_question']);
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $e_tags              = $e_card_group['tags'];
        $cg_name             = sanitize_text_field($e_card_group['name']);
        $image_type          = $e_card_group['image_type'];
        if (!in_array($image_type, get_default_image_display_type())) {
            Common::send_error('Please select a valid image display type');
        }
        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($whole_question)) {
            //				Common::send_error( 'Please provide a question' );
        }
        if (empty($e_cards)) {
            Common::send_error('No cards will be created');
        }
        if (empty($e_tags)) {
            Common::send_error('No tag selected');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }
        $cg_id      = (int) sanitize_text_field($e_card_group['id']);
        $card_group = CardGroup::find($cg_id);
        if (empty($card_group)) {
            Common::send_error('Invalid Card group');
        }

        Manager::beginTransaction();
        $card_group->whole_question = $whole_question;
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = false;
        $card_group->image_type     = $image_type;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }

        $c_numbers_updated = [];
        foreach ($e_cards as $one_card) {
            $question = wp_json_encode($one_card['question']);
            $answer   = wp_json_encode($one_card['answer']);
            $c_number = $one_card['c_number'];
            $card_id  = $one_card['id'];
            $hash     = $one_card['hash'];
            $card     = new Card();
            if (!empty($card_id)) {
                $card = Card::find($card_id);
                if (empty($card)) {
                    $card = new Card();
                }
            }
            $card->question      = $question;
            $card->answer        = $answer;
            $card->hash          = $hash;
            $card->c_number      = $c_number;
            $card->card_group_id = $card_group->id;
            $card->save();
            $c_numbers_updated[] = $c_number;
            //				Common::send_error( [
            //					'ajax_admin_create_new_basic_card',
            //					'post'                 => $post,
            //					'$one_card'            => $one_card,
            //					'$card_id'            => $card_id,
            //					'toSql'                => $card_group->toSql(),
            //					'$reverse'             => $reverse,
            //					'$question'            => $question,
            //					'$e_card_group'        => $e_card_group,
            //					'$whole_question'      => $whole_question,
            //					'$e_set_bg_as_default' => $e_set_bg_as_default,
            //					'$bg_image_id'         => $bg_image_id,
            //					'$answer'              => $answer,
            //					'$deck'                => $deck,
            //					'$card'                => $card,
            //					'$cg_name'             => $cg_name,
            //					'$e_tags'              => $e_tags,
            //					'$schedule_at'         => $schedule_at,
            //				] );
        }

        // Delete cards without not updated
        $cards_to_delete = $all_cards = CardGroup::find($cg_id)->cards()
            ->whereNotIn('c_number', $c_numbers_updated)->get()->pluck('id')->all();

        Answered::whereIn('card_id', $cards_to_delete)->forceDelete();
        Card::whereIn('id', $cards_to_delete)->forceDelete();
        //        $all_cards       = CardGroup::find($cg_id)->cards()
        //            ->whereNotIn('c_number', $c_numbers_updated)
        //            ->forceDelete();
        //
        //        Common::send_error([
        //            'ajax_admin_create_new_basic_card',
        //            'post'                  => $post,
        //            '$all_cards'            => $all_cards,
        //            'toSql'                 => $card_group->toSql(),
        //            '$reverse'              => $reverse,
        //            '$cards_to_delete'      => $cards_to_delete,
        //            'type $cards_to_delete' => gettype($cards_to_delete),
        //            '$e_card_group'         => $e_card_group,
        //            '$question'             => $question,
        //            '$e_set_bg_as_default'  => $e_set_bg_as_default,
        //            '$bg_image_id'          => $bg_image_id,
        //            '$answer'               => $answer,
        //            '$deck'                 => $deck,
        //            '$cg_name'              => $cg_name,
        //            '$e_tags'               => $e_tags,
        //            '$e_cards'              => $e_cards,
        //            '$schedule_at'          => $schedule_at,
        //            '$c_numbers_updated'    => $c_numbers_updated,
        //        ]);
        Manager::commit();
        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }

        $edit_page = Initializer::get_admin_url(Settings::SLUG_IMAGE_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Updated successfully.', $edit_page);

    }

    // <editor-fold desc="/Image Cards">

    // <editor-fold desc="Table Cards">

    public function ajax_admin_create_new_table_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_table_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_cards             = $all['cards'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $whole_question      = $e_card_group['whole_question'];
        $whole_question      = wp_json_encode($whole_question);
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $e_tags              = $e_card_group['tags'];
        $cg_name             = sanitize_text_field($e_card_group['name']);
        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($whole_question)) {
            //				Common::send_error( 'Please provide a question' );
        }
        if (empty($e_cards)) {
            Common::send_error('No cards will be created');
        }
        if (empty($e_tags)) {
            Common::send_error('No tag selected');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }


        $e_deck_id = $e_card_group['deck']['id'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }

        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$reverse'             => $reverse,
        //				'$whole_question'      => $whole_question,
        //				'$e_cards'             => $e_cards,
        //				'$e_card_group'        => $e_card_group,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        Manager::beginTransaction();
        $card_group                 = new CardGroup();
        $card_group->whole_question = $whole_question;
        $card_group->card_type      = 'table';
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = $reverse;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        foreach ($e_cards as $one_card) {
            $question            = wp_json_encode($one_card['question']);
            $answer              = wp_json_encode($one_card['answer']);
            $hash                = $one_card['hash'];
            $c_number            = $one_card['c_number'];
            $card                = new Card();
            $card->question      = $question;
            $card->hash          = $hash;
            $card->answer        = $answer;
            $card->c_number      = $c_number;
            $card->card_group_id = $card_group->id;
            $card->save();
            //				Common::send_error( [
            //					'ajax_admin_create_new_basic_card',
            //					'post'                 => $post,
            //					'$one_card'            => $one_card,
            //					'toSql'                => $card_group->toSql(),
            //					'$reverse'             => $reverse,
            //					'$hash'                => $hash,
            //					'$question'            => $question,
            //					'$e_card_group'        => $e_card_group,
            //					'$whole_question'      => $whole_question,
            //					'$e_set_bg_as_default' => $e_set_bg_as_default,
            //					'$bg_image_id'         => $bg_image_id,
            //					'$answer'              => $answer,
            //					'$deck'                => $deck,
            //					'$cg_name'             => $cg_name,
            //					'$e_tags'              => $e_tags,
            //					'$schedule_at'         => $schedule_at,
            //				] );

        }

        Manager::commit();

        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }
        // Create card group


        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        $edit_page = Initializer::get_admin_url(Settings::SLUG_TABLE_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Created successfully.', $edit_page);

    }

    public function ajax_admin_update_table_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_update_table_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_cards             = $all['cards'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $whole_question      = wp_json_encode($e_card_group['whole_question']);
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $e_tags              = $e_card_group['tags'];
        $cg_name             = sanitize_text_field($e_card_group['name']);

        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($whole_question)) {
            //				Common::send_error( 'Please provide a question' );
        }
        if (empty($e_cards)) {
            Common::send_error('No cards will be created');
        }
        if (empty($e_tags)) {
            Common::send_error('No tag selected');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }
        $cg_id      = (int) sanitize_text_field($e_card_group['id']);
        $card_group = CardGroup::find($cg_id);
        if (empty($card_group)) {
            Common::send_error('Invalid Card group');
        }
        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$e_cards'             => $e_cards,
        //				'$schedule_at'         => $schedule_at,
        //			] );
        Manager::beginTransaction();
        $card_group->whole_question = $whole_question;
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = false;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        $c_numbers_updated = [];
        foreach ($e_cards as $one_card) {
            $question = wp_json_encode($one_card['question']);
            $answer   = wp_json_encode($one_card['answer']);
            $c_number = $one_card['c_number'];
            $card_id  = $one_card['id'];
            $hash     = $one_card['hash'];
            $card     = new Card();
            if (!empty($card_id)) {
                $card = Card::find($card_id);
                if (empty($card)) {
                    $card = new Card();
                }
            }
            $card->question      = $question;
            $card->answer        = $answer;
            $card->hash          = $hash;
            $card->c_number      = $c_number;
            $card->card_group_id = $card_group->id;
            $card->save();
            $c_numbers_updated[] = $c_number;
            //				Common::send_error( [
            //					'ajax_admin_create_new_basic_card',
            //					'post'                 => $post,
            //					'$one_card'            => $one_card,
            //					'$card_id'            => $card_id,
            //					'toSql'                => $card_group->toSql(),
            //					'$reverse'             => $reverse,
            //					'$question'            => $question,
            //					'$e_card_group'        => $e_card_group,
            //					'$whole_question'      => $whole_question,
            //					'$e_set_bg_as_default' => $e_set_bg_as_default,
            //					'$bg_image_id'         => $bg_image_id,
            //					'$answer'              => $answer,
            //					'$deck'                => $deck,
            //					'$card'                => $card,
            //					'$cg_name'             => $cg_name,
            //					'$e_tags'              => $e_tags,
            //					'$schedule_at'         => $schedule_at,
            //				] );
        }
        Manager::commit();
        // Delete cards without not updated
        $all_cards = CardGroup::find($cg_id)->cards()
            ->whereNotIn('c_number', $c_numbers_updated)
            ->forceDelete();
        //
        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$all_cards'           => $all_cards,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$e_cards'             => $e_cards,
        //				'$schedule_at'         => $schedule_at,
        //				'$c_numbers_updated'   => $c_numbers_updated,
        //			] );

        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }

        $edit_page = Initializer::get_admin_url(Settings::SLUG_TABLE_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Updated successfully.', $edit_page);

    }

    // <editor-fold desc="Table Cards">

    // <editor-fold desc="Gap Cards">
    public function ajax_admin_update_gap_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_update_gap_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_cards             = $all['cards'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $whole_question      = $e_card_group['whole_question'];
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $e_tags              = $e_card_group['tags'];
        $cg_name             = sanitize_text_field($e_card_group['name']);

        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($whole_question)) {
            Common::send_error('Please provide a question');
        }
        if (empty($e_cards)) {
            Common::send_error('No cards will be created');
        }
        if (empty($e_tags)) {
            Common::send_error('No tag selected');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }
        $cg_id      = (int) sanitize_text_field($e_card_group['id']);
        $card_group = CardGroup::find($cg_id);
        if (empty($card_group)) {
            Common::send_error('Invalid Card group');
        }

        Manager::beginTransaction();
        $card_group->whole_question = $whole_question;
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = false;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        $c_numbers_updated = [];
        foreach ($e_cards as $one_card) {
            $question = $one_card['question'];
            $answer   = $one_card['answer'];
            $c_number = $one_card['c_number'];
            $card_id  = $one_card['id'];
            $hash     = $one_card['hash'];
            $card     = new Card();
            if (!empty($card_id)) {
                $card = Card::find($card_id);
                if (empty($card)) {
                    $card = new Card();
                }
            }
            $card->question      = $question;
            $card->answer        = $answer;
            $card->hash          = $hash;
            $card->c_number      = $c_number;
            $card->card_group_id = $card_group->id;
            $card->save();
            $c_numbers_updated[] = $c_number;
            //				Common::send_error( [
            //					'ajax_admin_create_new_basic_card',
            //					'post'                 => $post,
            //					'$one_card'            => $one_card,
            //					'$card_id'            => $card_id,
            //					'toSql'                => $card_group->toSql(),
            //					'$reverse'             => $reverse,
            //					'$question'            => $question,
            //					'$e_card_group'        => $e_card_group,
            //					'$whole_question'      => $whole_question,
            //					'$e_set_bg_as_default' => $e_set_bg_as_default,
            //					'$bg_image_id'         => $bg_image_id,
            //					'$answer'              => $answer,
            //					'$deck'                => $deck,
            //					'$card'                => $card,
            //					'$cg_name'             => $cg_name,
            //					'$e_tags'              => $e_tags,
            //					'$schedule_at'         => $schedule_at,
            //				] );
        }
        Manager::commit();
        // Delete cards without not updated
        $all_cards = CardGroup::find($cg_id)->cards()
            ->whereNotIn('c_number', $c_numbers_updated)
            ->forceDelete();


        //
        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$all_cards'           => $all_cards,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$e_cards'             => $e_cards,
        //				'$schedule_at'         => $schedule_at,
        //				'$c_numbers_updated'   => $c_numbers_updated,
        //			] );


        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }

        $edit_page = Initializer::get_admin_url(Settings::SLUG_GAP_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Updated successfully.', $edit_page);

    }

    public function ajax_admin_create_new_gap_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_gap_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_cards             = $all['cards'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $whole_question      = $e_card_group['whole_question'];
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $e_tags              = $e_card_group['tags'];
        $cg_name             = sanitize_text_field($e_card_group['name']);
        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($whole_question)) {
            Common::send_error('Please provide a question');
        }
        if (empty($e_cards)) {
            Common::send_error('No cards will be created');
        }
        if (empty($e_tags)) {
            Common::send_error('No tag selected');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }

        Manager::beginTransaction();
        $card_group                 = new CardGroup();
        $card_group->whole_question = $whole_question;
        $card_group->card_type      = 'gap';
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = $reverse;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        foreach ($e_cards as $one_card) {
            $question            = $one_card['question'];
            $answer              = $one_card['answer'];
            $hash                = $one_card['hash'];
            $c_number            = $one_card['c_number'];
            $card                = new Card();
            $card->question      = $question;
            $card->hash          = $hash;
            $card->answer        = $answer;
            $card->c_number      = $c_number;
            $card->card_group_id = $card_group->id;
            $card->save();
            //				Common::send_error( [
            //					'ajax_admin_create_new_basic_card',
            //					'post'                 => $post,
            //					'$one_card'            => $one_card,
            //					'toSql'                => $card_group->toSql(),
            //					'$reverse'             => $reverse,
            //					'$hash'                => $hash,
            //					'$question'            => $question,
            //					'$e_card_group'        => $e_card_group,
            //					'$whole_question'      => $whole_question,
            //					'$e_set_bg_as_default' => $e_set_bg_as_default,
            //					'$bg_image_id'         => $bg_image_id,
            //					'$answer'              => $answer,
            //					'$deck'                => $deck,
            //					'$cg_name'             => $cg_name,
            //					'$e_tags'              => $e_tags,
            //					'$schedule_at'         => $schedule_at,
            //				] );

        }

        Manager::commit();

        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }
        // Create card group


        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$e_card'              => $e_card,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        $edit_page = Initializer::get_admin_url(Settings::SLUG_GAP_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Created successfully.', $edit_page);

    }

    public function ajax_admin_load_cards_groups($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //        			Common::send_error( [
        //        				'ajax_admin_load_deck_group',
        //        				'post'            => $post,
        //        				'$params'         => $params,
        //        				'$per_page'       => $per_page,
        //        				'$page'           => $page,
        //        				'$search_keyword' => $search_keyword,
        //        				'$status'         => $status,
        //        			] );

        $card_groups = CardGroup::get_card_groups([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);
        $totals      = CardGroup::get_totals();

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$card_groups'    => $card_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Card group loaded.', [
            'details' => $card_groups,
            'totals'  => $totals,
        ], [
            //				'post' => $post,
        ]);

    }

    // </editor-fold desc="Gap Cards">

    // <editor-fold desc="Basic Cards">
    public function ajax_admin_delete_card_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_trash_cards',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'card_groups' => [],
            ]);
        Manager::beginTransaction();
        foreach ($args['card_groups'] as $card_group) {
            $id    = (int) sanitize_text_field($card_group['id']);
            $group = CardGroup::with('cards', 'tags')->withTrashed()->find($id);
            $group->tags()->detach();
            $group->cards()->withTrashed()->forceDelete();
            $group->forceDelete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'        => $post,
            //					'$card_group' => $card_group,
            //					'$cards'      => $cards,
            //					'$all'        => $all,
            //					'$id'         => $id,
            //					'$args'       => $args,
            //					'$group'      => $group,
            //				] );
        }

        Manager::commit();

        Common::send_success('Deleted successfully.');

    }

    public function ajax_admin_restore_card_group($post): void {
//        Common::send_error([
//            'ajax_admin_restore_card_group',
//            'post' => $post,
//        ]);

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'card_groups' => [],
            ]);
        Manager::beginTransaction();
        foreach ($args['card_groups'] as $card_group) {
            $id    = (int) sanitize_text_field($card_group['id']);
            $group = CardGroup::withTrashed()->with('cards')->find($id);

            $the_cards = $group->cards()->withTrashed()->get();
            foreach ($the_cards as $card) {
                $card->answered()->withTrashed()->restore();
                $card->restore();
                //                Common::send_error([
                //                    'ajax_admin_trash_cards',
                //                    'post'              => $post,
                //                    '$the_cards'        => $the_cards,
                //                    '$card->answered()' => $card->answered()->get(),
                //                ]);
            }
            $group->restore();
//            Common::send_error([
//                'ajax_admin_trash_cards',
//                'post'       => $post,
//                '$the_cards' => $the_cards,
//            ]);

            //            $group->cards()->each(function($card){
            //                $card->answered()->delete();
            //            });
            //				Deck::query()->where( 'id', '=', $id )->delete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$card_group'  => $card_group,
            //					'$all'  => $all,
            //					'$id'   => $id,
            //					'$args' => $args,
            //					'$group' => $group,
            //				] );
        }

        Manager::commit();

        Common::send_success('Restored successfully.');

    }

    public function ajax_admin_trash_cards($post): void {
        //			Common::send_error( [
        //				'ajax_admin_trash_cards',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'card_groups' => [],
            ]);
        Manager::beginTransaction();
        foreach ($args['card_groups'] as $card_group) {
            $id    = (int) sanitize_text_field($card_group['id']);
            $group = CardGroup::with('cards')->find($id);

            $the_cards = $group->cards()->get();
            foreach ($the_cards as $card) {
                $card->answered()->delete();
                $card->delete();
                //                Common::send_error([
                //                    'ajax_admin_trash_cards',
                //                    'post'              => $post,
                //                    '$the_cards'        => $the_cards,
                //                    '$card->answered()' => $card->answered()->get(),
                //                ]);
            }
            $group->delete();
            //            Common::send_error([
            //                'ajax_admin_trash_cards',
            //                'post'       => $post,
            //                '$the_cards' => $the_cards,
            //            ]);
            //            $group->cards()->each(function($card){
            //                $card->answered()->delete();
            //            });
            //				Deck::query()->where( 'id', '=', $id )->delete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$card_group'  => $card_group,
            //					'$all'  => $all,
            //					'$id'   => $id,
            //					'$args' => $args,
            //					'$group' => $group,
            //				] );
        }

        Manager::commit();

        Common::send_success('Trashed successfully.');

    }

    public function admin_update_basic_card($post): void {
        //			Common::send_error( [
        //				'admin_update_basic_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_card              = $all['card'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $e_card_group_id     = $e_card_group['id'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $question            = $e_card_group['whole_question'];
        $answer              = $e_card['answer'];
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $e_cards             = $e_card_group['cards'];
        $cg_name             = sanitize_text_field($e_card_group['name']);
        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_cards)) {
            Common::send_error('The card is empty');
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($question)) {
            Common::send_error('Please provide a question');
        }
        if (empty($answer)) {
            Common::send_error('Please provide an answer');
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $e_tags    = $e_card_group['tags'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }
        $card_group = CardGroup::find($e_card_group_id);
        if (empty($card_group)) {
            Common::send_error('Invalid card group');
        }
        $card_id = $e_cards[0]['id'];
        $card    = Card::find($card_id);
        if (empty($card)) {
            Common::send_error('Invalid card');
        }

        Manager::beginTransaction();

        $card_group->whole_question = $question;
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        $card->question = $question;
        $card->answer   = $answer;
        $card->save();
        Manager::commit();

        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }
        // Create card group


        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$e_card'              => $e_card,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$card'                => $card,
        //				'$e_card_group_id'     => $e_card_group_id,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        $edit_page = Initializer::get_admin_url(Settings::SLUG_BASIC_CARD)
            .'&action=card-edit'
            .'&card-group='.$card_group->id;

        Common::send_success('Updated successfully.', $edit_page);

    }

    public function ajax_admin_create_new_basic_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post' => $post,
        //			] );

        $all                 = $post[Common::VAR_2];
        $e_card              = $all['card'];
        $e_card_group        = $all['cardGroup'];
        $e_deck              = $e_card_group['deck'];
        $bg_image_id         = (int) sanitize_text_field($e_card_group['bg_image_id']);
        $question            = $e_card_group['whole_question'];
        $answer              = $e_card['answer'];
        $hash                = $e_card['hash'];
        $e_set_bg_as_default = $all['set_bg_as_default'];
        $schedule_at         = $e_card_group['scheduled_at'];
        $reverse             = $e_card_group['reverse'];
        $cg_name             = sanitize_text_field($e_card_group['name']);
        if (empty($schedule_at)) {
            $schedule_at = Common::getDateTime();
        } else {
            $schedule_at = Common::format_datetime($schedule_at);
        }
        if (empty($e_deck)) {
            Common::send_error('Please select a deck');
        }
        if (empty($question)) {
            Common::send_error('Please provide a question');
        }
        if (empty($answer)) {
            Common::send_error('Please provide an answer');
        }
        if (empty($bg_image_id)) {
            $bg_image_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (empty($bg_image_id)) {
                Common::send_error('Please select a background image.');
            }
        }

        $e_deck_id = $e_card_group['deck']['id'];
        $e_tags    = $e_card_group['tags'];
        $deck      = Deck::find($e_deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }

        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$e_card'              => $e_card,
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        Manager::beginTransaction();
        $card_group                 = new CardGroup();
        $card_group->whole_question = $question;
        $card_group->card_type      = 'basic';
        $card_group->scheduled_at   = $schedule_at;
        $card_group->bg_image_id    = $bg_image_id;
        $card_group->name           = $cg_name;
        $card_group->deck_id        = $e_deck_id;
        $card_group->reverse        = $reverse;
        $card_group->save();
        $card_group->tags()->detach();
        foreach ($e_tags as $one) {
            $tag_id = $one['id'];
            $tag    = Tag::find($tag_id);
            if (!empty($tag)) {
                $card_group->tags()->save($tag);
            }
        }
        $card                = new Card();
        $card->question      = $question;
        $card->answer        = $answer;
        $card->hash          = $hash;
        $card->c_number      = 'c1';
        $card->card_group_id = $card_group->id;
        $card->save();
        Manager::commit();

        if ($e_set_bg_as_default) {
            update_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id);
        }
        // Create card group


        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'                 => $post,
        //				'$e_card'              => $e_card,
        //				'toSql'                => $card_group->toSql(),
        //				'$reverse'             => $reverse,
        //				'$e_card_group'        => $e_card_group,
        //				'$question'            => $question,
        //				'$e_set_bg_as_default' => $e_set_bg_as_default,
        //				'$bg_image_id'         => $bg_image_id,
        //				'$answer'              => $answer,
        //				'$deck'                => $deck,
        //				'$cg_name'             => $cg_name,
        //				'$e_tags'              => $e_tags,
        //				'$schedule_at'         => $schedule_at,
        //			] );

        $edit_page = Initializer::get_admin_url(Settings::SLUG_BASIC_CARD)
            .'&card-group='.$card_group->id;

        Common::send_success('Created successfully.', $edit_page);

    }

    public function ajax_admin_load_basic_card($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post' => $post,
        //			] );

        $all           = $post[Common::VAR_2];
        $card_group_id = (int) sanitize_text_field($all['card_group_id']);

        $card_group = CardGroup::with('tags', 'cards', 'deck')->find($card_group_id);
        if (empty($card_group)) {
            Common::send_error('Invalid card group');
        }
        //			if ( 'table' === $card_group->card_type ) {
        //				$card_group->whole_question = json_decode( $card_group->whole_question );
        //				foreach ( $card_group->cards as $card ) {
        //					$card->question = json_decode( $card->question );
        //					$card->answer   = json_decode( $card->answer );
        //				}
        //			} elseif ( 'image' === $card_group->card_type ) {
        //				$card_group->whole_question = json_decode( $card_group->whole_question );
        //				foreach ( $card_group->cards as $card ) {
        //					$card->question = json_decode( $card->question );
        //					$card->answer   = json_decode( $card->answer );
        //				}
        //			}

        //			$cards = $card_group->cards;
        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'           => $post,
        //				'$card_group_id' => $card_group_id,
        //				'$card_group'    => $card_group,
        ////				'$cards'         => $cards,
        //			] );


        Common::send_success('Loaded successfully.', [
            'card_group' => $card_group,
        ]);

    }
    // </editor-fold desc="Basic Cards">

    // <editor-fold desc="Tags">
    public function ajax_admin_create_tag($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_tag',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $name = sanitize_text_field($all['name']);

        $create = Tag::firstOrCreate(['name' => $name]);

        //			Common::send_error( [
        //				'ajax_admin_create_tag',
        //				'post'  => $post,
        //				'$name' => $name,
        //			] );

        Common::send_success('Created successfully.', $create);

    }

    public function ajax_admin_search_tags($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_tags',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //			Common::send_error( [
        //				'ajax_admin_load_tags',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$status'         => $status,
        //			] );

        $items  = Tag::get_tags([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);
        $totals = Tag::get_totals();

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$deck_groups'    => $deck_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Tag loaded.', [
            'details' => $items,
            'totals'  => $totals,
        ], [
            //				'post' => $post,
        ]);

    }

    public function ajax_admin_load_tags($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_tags',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //			Common::send_error( [
        //				'ajax_admin_load_tags',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$status'         => $status,
        //			] );

        $items  = Tag::get_tags([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);
        $totals = Tag::get_totals();

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$deck_groups'    => $deck_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Tag loaded.', [
            'details' => $items,
            'totals'  => $totals,
        ], [
            //				'post' => $post,
        ]);

    }

    public function ajax_admin_trash_tags($post): void {
        //			Common::send_error( [
        //				'ajax_admin_trash_tags',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'items' => [],
            ]);
        foreach ($args['items'] as $one) {
            $id = (int) sanitize_text_field($one['id']);
            Tag::query()->where('id', '=', $id)->delete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$all'  => $all,
            //					'$id'   => $id,
            //					'$args' => $args,
            //					'$one' => $one,
            //				] );
        }


        Common::send_success('Trashed successfully.');

    }

    public function ajax_admin_delete_tags($post): void {
        //			Common::send_error( [
        //				'ajax_admin_delete_tags',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'items' => [],
            ]);
        foreach ($args['items'] as $one) {
            $id = (int) sanitize_text_field($one['id']);
            Tag::query()->where('id', '=', $id)->forceDelete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$all'  => $all,
            //					'$name' => $name,
            //					'$id'   => $id,
            //					'$args' => $args,
            //				] );
        }


        Common::send_success('Deleted.');

    }

    // </editor-fold desc="Tags">

    // <editor-fold desc="Deck">

    public function ajax_admin_trash_decks($post): void {
        //			Common::send_error( [
        //				'ajax_admin_trash_deck_group',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'decks' => [],
            ]);
        foreach ($args['decks'] as $item) {
            $id = (int) sanitize_text_field($item['id']);
            Deck::find($id)->delete();
            //				Deck::query()->where( 'id', '=', $id )->delete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$all'  => $all,
            //					'$id'   => $id,
            //					'$args' => $args,
            //				] );
        }


        Common::send_success('Trashed successfully.');

    }

    public function ajax_admin_delete_decks($post): void {
        //			Common::send_error( [
        //				'ajax_admin_trash_deck_group',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'decks' => [],
            ]);

        foreach ($args['decks'] as $item) {
            Manager::beginTransaction();
            $id                    = (int) sanitize_text_field($item['id']);
            $uncategorized_deck_id = get_uncategorized_deck_id();
            CardGroup
                ::withTrashed()
                ->where('deck_id', '=', $id)
                ->update([
                    'deck_id' => $uncategorized_deck_id,
                ]);
            $deck = Deck::withTrashed()->find($id);
            $deck->tags()->detach();
            $deck->forceDelete();
            Manager::commit();
            //				Deck::query()->where( 'id', '=', $id )->delete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$all'  => $all,
            //					'$id'   => $id,
            //					'$args' => $args,
            //				] );
        }


        Common::send_success('Deleted successfully.');

    }

    public function ajax_admin_load_decks($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$status'         => $status,
        //			] );

        $decks  = Deck::get_decks([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);
        $totals = Deck::get_totals();

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$deck_groups'    => $deck_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Decks loaded.', [
            'details' => $decks,
            'totals'  => $totals,
        ], [
            'post' => $post,
        ]);

    }

    public function ajax_admin_search_decks($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$status'         => $status,
        //			] );

        $items = Deck::get_deck_simple([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$deck_groups'    => $deck_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Decks  found.', $items);

    }

    public function ajax_admin_create_new_deck($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_deck',
        //				'post' => $post,
        //			] );

        $all        = $post[Common::VAR_2];
        $name       = sanitize_text_field($all['name']);
        $deck_group = $all['deck_group'];
        $tags       = $all['tags'];

        if (empty($deck_group)) {
            Common::send_error('Please select a deck group');
        }

        $deck_group_id = (int) sanitize_text_field($deck_group['id']);
        try {


            Manager::beginTransaction();
            $deck_group = DeckGroup::find($deck_group_id);
            $deck       = new Deck();
            $deck->name = $name;
            $deck->deck_group()->associate($deck_group);
            $deck->save();

            $deck->tags()->detach();
            foreach ($tags as $one) {
                $tag = Tag::find($one['id']);
                $deck->tags()->save($tag);
                //				Common::send_error( [
                //					'ajax_admin_create_new_deck_group',
                //					'post'           => $post,
                //					'$deck_group_id' => $deck_group_id,
                //					'$tags'          => $tags,
                //					'$name'          => $name,
                //					'$tag'           => $tag,
                ////				'$deck_group'      => $deck_group,
                //				] );
            }
            Manager::commit();
        } catch (PDOException $e) {
            Common::send_error('Item already exists');
        }
        //			Common::send_error( [
        //				'ajax_admin_create_new_deck_group',
        //				'post'           => $post,
        //				'$deck_group_id' => $deck_group_id,
        //				'$tags'          => $tags,
        //				'$name'          => $name,
        //			] );

        Common::send_success('Deck created.');

    }

    public function ajax_admin_update_decks($post): void {
        //			Common::send_error( [
        //				'ajax_admin_update_decks',
        //				'post' => $post,
        //			] );

        $all = $post[Common::VAR_2];

        $decks = $all['decks'];
        foreach ($decks as $one_deck) {
            $name          = sanitize_text_field($one_deck['name']);
            $e_deck_group  = $one_deck['deck_group'];
            $tags          = $one_deck['tags'];
            $deck_id       = (int) sanitize_text_field($one_deck['id']);
            $deck_group_id = (int) sanitize_text_field($e_deck_group['id']);

            if (empty($e_deck_group)) {
                Common::send_error('Please select a deck group');
            }

            $deck = Deck::find($deck_id);
            $deck->update(['name' => $name, 'deck_group_id' => $deck_group_id]);
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'           => $post,
            //					'$deck_group_id' => $deck_group_id,
            //					'$tags'          => $tags,
            //					'$name'          => $name,
            //					'$e_deck_group'  => $e_deck_group,
            //				] );
            $deck->tags()->detach();
            foreach ($tags as $one) {
                $tag_id = (int) sanitize_text_field($one['id']);
                $tag    = Tag::find($tag_id);
                $deck->tags()->save($tag);
                //				Common::send_error( [
                //					'ajax_admin_create_new_deck_group',
                //					'post'           => $post,
                //					'$deck_group_id' => $deck_group_id,
                //					'$tags'          => $tags,
                //					'$name'          => $name,
                //					'$tag'           => $tag,
                ////				'$deck_group'      => $deck_group,
                //				] );
            }
        }

        Common::send_success('Saved.');

    }

    // </editor-fold desc="Deck">

    // <editor-fold desc="Deck Groups">

    public function ajax_admin_search_deck_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$status'         => $status,
        //			] );

        $deck_groups = DeckGroup::get_deck_groups_simple([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$deck_groups'    => $deck_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Deck group found.', $deck_groups);

    }

    public function ajax_admin_load_deck_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post' => $post,
        //			] );

        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$status'         => $status,
        //			] );

        $deck_groups = DeckGroup::get_deck_groups([
            'search'       => $search_keyword,
            'page'         => $page,
            'per_page'     => $per_page,
            'only_trashed' => ('trash' === $status) ? true : false,
        ]);
        $totals      = DeckGroup::get_totals();

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'            => $post,
        //				'$params'         => $params,
        //				'$per_page'       => $per_page,
        //				'$page'           => $page,
        //				'$search_keyword' => $search_keyword,
        //				'$deck_groups'    => $deck_groups,
        //				'$status'         => $status,
        //			] );


        Common::send_success('Deck group loaded.', [
            'details' => $deck_groups,
            'totals'  => $totals,
        ], [
            'post' => $post,
        ]);

    }

    public function ajax_admin_trash_deck_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_trash_deck_group',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'deck_groups' => [],
            ]);
        foreach ($args['deck_groups'] as $group) {
            $id = (int) sanitize_text_field($group['id']);
            DeckGroup::query()->where('id', '=', $id)->delete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$all'  => $all,
            //					'$id'   => $id,
            //					'$args' => $args,
            //				] );
        }


        Common::send_success('Trashed successfully.');

    }

    public function ajax_admin_delete_deck_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_delete_deck_group',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'deck_groups' => [],
            ]);
        Manager::beginTransaction();
        foreach ($args['deck_groups'] as $group) {
            $id = (int) sanitize_text_field($group['id']);

            // Assign uncategorized deck group to existing decks under this deck group
            $uncategorized_deck_group_id = get_uncategorized_deck_group_id();
            if ($uncategorized_deck_group_id) {
                Deck
                    ::where('deck_group_id', '=', $id)
                    ->update([
                        'deck_group_id' => $uncategorized_deck_group_id,
                    ]);
            }
            // Delete the deck group
            $deck_group = DeckGroup::withTrashed()->find($id);
            $deck_group->tags()->detach();
            $deck_group->forceDelete();

            //				DeckGroup::query()->where( 'id', '=', $id )->forceDelete();
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'  => $post,
            //					'$all'  => $all,
            //					'$name' => $name,
            //					'$id'   => $id,
            //					'$args' => $args,
            //				] );
        }
        Manager::commit();

        Common::send_success('Deleted.');

    }

    public function ajax_admin_update_deck_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_update_deck_group',
        //				'post' => $post,
        //			] );

        $all  = $post[Common::VAR_2];
        $args = wp_parse_args(
            $all,
            [
                'deck_groups' => [],
            ]);
        foreach ($args['deck_groups'] as $group) {
            $name       = sanitize_text_field($group['name']);
            $id         = (int) sanitize_text_field($group['id']);
            $update_id  = DeckGroup::query()->where('id', '=', $id)->update([
                'name' => $name,
            ]);
            $deck_group = DeckGroup::find($id);
            $deck_group->tags()->detach();
            foreach ($group['tags'] as $one) {
                $tag_id = (int) sanitize_text_field($one['id']);
                $tag    = Tag::find($tag_id);
                $deck_group->tags()->save($tag);
            }

            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'        => $post,
            //					'$all'        => $all,
            //					'$name'       => $name,
            //					'$id'         => $id,
            //					'$args'       => $args,
            //					'$group'      => $group,
            //					'$deck_group' => $deck_group,
            //					'$update_id'  => $update_id,
            //				] );
        }


        Common::send_success('Saved.');

    }

    public function ajax_admin_create_new_deck_group($post): void {
        //			Common::send_error( [
        //				'ajax_admin_create_new_deck_group',
        //				'post' => $post,
        //			] );

        $all             = $post[Common::VAR_2];
        $deck_group_name = sanitize_text_field($all['deck_group_name']);
        $tags            = $all['tags'];

        $create     = DeckGroup::firstOrCreate(['name' => $deck_group_name]);
        $deck_group = DeckGroup::find($create->id);
        $deck_group->tags()->detach();
        foreach ($tags as $one) {
            $tag = Tag::find($one['id']);
            $deck_group->tags()->save($tag);
            //				Common::send_error( [
            //					'ajax_admin_create_new_deck_group',
            //					'post'             => $post,
            //					'toSql'             => $deck_group->tags()->toSql(),
            //					'$deck_group_name' => $deck_group_name,
            //					'$tags'            => $tags,
            //					'$tag'            => $tag,
            ////				'$deck_group'      => $deck_group,
            //				] );
        }


        //			Common::send_error( [
        //				'ajax_admin_create_new_deck_group',
        //				'post'             => $post,
        //				'$deck_group_name' => $deck_group_name,
        //				'$tags'            => $tags,
        ////				'$deck_group'      => $deck_group,
        //			] );

        Common::send_success('Deck group created.');

    }

    // </editor-fold  desc="Deck Groups" >

    // <editor-fold desc="Others">
    public function ajax_admin_load_image_attachment($post): void {

        //			Common::send_error( [
        //				'ajax_admin_load_image_attachment',
        //				'post' => $post,
        //			] );

        $all = $post[Common::VAR_2];
        $id  = (int) sanitize_text_field($all['id']);
        if ($id < 1) {
            Common::send_error('Invalid image id');
        }
        $attachment_url = wp_get_attachment_image_url($id, 'full');
        if (empty($attachment_url)) {
            $default_bg_id = get_option(Settings::OP_DEFAULT_CARD_BG_IMAGE, 0);
            if (!empty($default_bg_id)) {
                $attachment_url = wp_get_attachment_image_url($default_bg_id, 'full');
            }
        }

        //			Common::send_error( [
        //				'ajax_admin_create_new_basic_card',
        //				'post'        => $post,
        //				'$id'         => $id,
        //				'$attachment_url' => $attachment_url,
        //			] );


        Common::send_success('Image found', $attachment_url);

    }
    // </editor-fold desc="Others">

}