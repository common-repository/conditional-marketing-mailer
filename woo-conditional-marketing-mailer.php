<?php
/*
 * Plugin Name: WooCommerce Abandonment Cart Recovery
 * Description: This plugin lets you create updates to be sent via Email based on custom conditions. Fully customizable and easy to use.
 * Version: 2.7
 * Author: wp-buy
 * Text Domain: conditional-marketing-mailer
 * Domain Path: /languages
 * Author URI: https://profiles.wordpress.org/wp-buy/#content-plugins
 * License: GPL2
*/

define( 'WCMM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'WCMM_PLUGIN_URL', plugin_dir_url(__FILE__) );

include_once('notifications.php');

 //---------------------------------------------------------------------------------------------
//Load plugin textdomain to load translations
//---------------------------------------------------------------------------------------------

if (!function_exists('WCMM_conditional_marketing_mailer_free_load_textdomain')){
    function WCMM_conditional_marketing_mailer_free_load_textdomain() {
        load_plugin_textdomain( 'conditional-marketing-mailer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    add_action( 'init', 'WCMM_conditional_marketing_mailer_free_load_textdomain' );
}



require_once( WCMM_PLUGIN_DIR . '/admin/post_type.php' );
require_once( WCMM_PLUGIN_DIR . '/admin/metaBox.php' );
require_once( WCMM_PLUGIN_DIR . '/admin/query_builder.php' );
require_once( WCMM_PLUGIN_DIR . '/admin/cron.php' );
require_once( WCMM_PLUGIN_DIR . '/admin/testing-massage.php' );
if (!function_exists('WCMM_show_woocommerce_not_installed_admin_notice')) {

    function WCMM_show_woocommerce_not_installed_admin_notice()
    {
        echo '<div class="error notice-warning is-dismissible"><p>';
        _e('This plugin is designed to work with the WooCommerce core plugin, Please install WooCommerce plugin first!.', 'conditional-marketing-mailer');
        echo '</p></div>';
        exit;
    }
}

if (is_admin()) {
    add_action( 'load-edit.php', function(){
        if (  class_exists( 'woocommerce' ) ) { return true; }
        $screen = get_current_screen();

        if( 'edit-wcmm' === $screen->id )
        {
            // Before:
            add_action( 'all_admin_notices', "WCMM_show_woocommerce_not_installed_admin_notice");
        }
    });
    add_action( 'load-post-new.php', function(){
        if (  class_exists( 'woocommerce' ) ) { return true; }
        $screen = get_current_screen();

        if( 'wcmm' === $screen->id )
        {
            // Before:
            add_action( 'all_admin_notices', "WCMM_show_woocommerce_not_installed_admin_notice");
        }
    });
}


if (!function_exists('WCMM_duplicate_post_link')) {

    function WCMM_duplicate_post_link($actions, $post)
    {
        if ($post->post_type == 'wcmm') {
            $actions['duplicate'] = sprintf('<a href="%s" title="" rel="permalink">%s</a>', get_admin_url('', 'edit.php?post_type=mail_send&query_id=' . $post->ID), __('Statistics', 'conditional-marketing-mailer'));
        }
        return $actions;
    }

    add_filter('post_row_actions', 'WCMM_duplicate_post_link', 10, 2);
}

if (!function_exists('WCMM_wisdom_sort_plugins_by_slug')) {
    function WCMM_wisdom_sort_plugins_by_slug($query)
    {
        global $pagenow;

        if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'mail_send' && isset($_GET['query_id']) && $_GET['query_id'] != '') {
            $query->query_vars['meta_key'] = 'query_id';
            $query->query_vars['meta_value'] = sanitize_title(esc_sql($_GET['query_id']));
            $query->query_vars['meta_compare'] = '=';
        }
    }

    add_filter('parse_query', 'WCMM_wisdom_sort_plugins_by_slug');
}

if (!function_exists('WCMM_wisdom_sort_plugins_by_slug')) {
    add_filter('WCMM_wisdom_sort_plugins_by_slug', 'WCMM_set_custom_edit_book_columns');
    function WCMM_set_custom_edit_book_columns($columns)
    {
        $columns['massage_opened'] = __('massage status', 'conditional-marketing-mailer');
        return $columns;
    }
}
// Add the data to the custom columns for the book post type:

if (!function_exists('WCMM_custom_book_column')) {
    add_action('manage_mail_send_posts_custom_column', 'WCMM_custom_book_column', 10, 2);
    function WCMM_custom_book_column($column, $post_id)
    {
        switch ($column) {
            case 'massage_opened' :
                $opentd = get_post_meta(absint($post_id), 'opened', true);
                if (!empty($opentd)) {
                    _e('Opened', 'conditional-marketing-mailer');
                } else {
                    _e('Not Opened', 'conditional-marketing-mailer');
                }
                break;

        }
    }
}


add_action('woocommerce_checkout_update_order_meta',function( $order_id, $posted ) {
    $order = wc_get_order( $order_id );
    $items = $order->get_items();
    $product_id_array = array();
    if(!empty($items)){
        foreach ( $items as $item ) {
            $product_id_array[] = $item->get_product_id();
            add_post_meta( $order_id, '_product_id_', $item->get_product_id() );
        }
    }
    if(!empty($product_id_array)){
        $cat_array = array();
        $tag_array = array();

        foreach ($product_id_array as $p_id) {
            $terms_post_cat = get_the_terms($p_id, 'product_cat');
            if (!empty($terms_post_cat)) {
                foreach ($terms_post_cat as $term_cat) {
                    $term_cat_id = $term_cat->term_id;
                    $key = array_search($term_cat_id, $cat_array);
                    if ($key == '') {
                        add_post_meta($order_id, '_product_cat_id_', $term_cat_id);
                        $cat_array[] = $term_cat_id;
                    }
                }
            }

            $terms_post_tag = get_the_terms($p_id, 'product_tag');
            if(!empty($terms_post_tag)) {
                if($key == '') {
                    foreach ($terms_post_tag as $term_tag) {
                        $term_tag_id = $term_tag->term_id;
                        $key = array_search($term_tag_id, $tag_array);
                        if($key == '') {
                            add_post_meta($order_id, '_product_tag_id_', $term_tag_id);
                            $tag_array[] = $term_tag_id;
                        }
                    }
                }
            }
        }
    }
} , 10, 2);

//---------------------------------------- Add plugin settings link to Plugins page
if (!function_exists('WCMM_plugin_add_settings_link')) {

    function WCMM_plugin_add_settings_link($links)
    {
        $settings_link = '<a href="edit.php?post_type=wcmm">' . __('Conditions list', 'conditional-marketing-mailer') . '</a>';
        array_push($links, $settings_link);

        $add_new_link = '<a href="post-new.php?post_type=wcmm">' . __('Add new', 'conditional-marketing-mailer') . '</a>';
        array_push($links, $add_new_link);

        return $links;
    }

    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'WCMM_plugin_add_settings_link');
}
//--------------------------------------
