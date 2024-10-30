<?php
if (  !class_exists( 'WCMM_create_cancel_order_post_type' ) ) {

    class WCMM_create_cancel_order_post_type
    {
        function __construct()
        {
            add_action('init', array($this, 'wpdocs_codex_book_init'));
            add_action('init', array($this, 'mail_post'));
            add_action('admin_footer', array($this, 'disable_new_posts'));
            add_filter('manage_wcmm_posts_columns', array($this, 'filter_cpt_columns'));
            add_action('manage_posts_custom_column', array($this, 'action_custom_columns_content'), 10, 2);
            add_filter('post_row_actions', array($this, 'my_custom_bulk_actions'), 10, 2);


        }

        function wpdocs_codex_book_init()
        {

            $labels = array(
                'name' => _x('WooCommerce Abandonment Cart Recovery', 'Post type general name', 'conditional-marketing-mailer'),
                'singular_name' => _x('Cart Recovery', 'Post type singular name', 'conditional-marketing-mailer'),
                'menu_name' => _x('Cart Recovery', 'Admin Menu text', 'conditional-marketing-mailer'),
                'name_admin_bar' => _x('WOO Cart Recovery', 'Add New on Toolbar', 'conditional-marketing-mailer'),
                'add_new' => __('Add New', 'conditional-marketing-mailer'),
                'add_new_item' => __('Add New ', 'conditional-marketing-mailer'),
                'new_item' => __('New ', 'conditional-marketing-mailer'),
                'edit_item' => __('Edit ', 'conditional-marketing-mailer'),
                'view_item' => __('View ', 'conditional-marketing-mailer'),
                'all_items' => __('Conditions list', 'conditional-marketing-mailer'),
                'search_items' => __('Search', 'conditional-marketing-mailer'),
                'parent_item_colon' => __('Parent:', 'conditional-marketing-mailer'),
                'not_found' => __('No found.', 'conditional-marketing-mailer'),
                'not_found_in_trash' => __('No found in Trash.', 'conditional-marketing-mailer'),
                'archives' => _x('Book archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'conditional-marketing-mailer'),
                'insert_into_item' => _x('Insert into WOO Mailer', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'conditional-marketing-mailer'),
                'filter_items_list' => _x('Filter WOO Mailer list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'conditional-marketing-mailer'),
                'items_list_navigation' => _x('WOO Mailer list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'conditional-marketing-mailer'),
                'items_list' => _x('WOO Mailer list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'conditional-marketing-mailer'),
            );

            $args = array(
                'labels' => $labels,
                'public' => false,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'wcmm'),
                'capability_type' => 'page',
                'has_archive' => false,
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title'),
				'menu_icon' => 'dashicons-cart'
            );

            register_post_type('wcmm', $args);
        }


        function mail_post()
        {
            $query_id = (isset($_GET['query_id'])) ? absint($_GET['query_id']) : '';
            $labels = array(
                'name' => _x('Statistics (' . get_the_title($query_id) . ')', 'Post type general name', 'conditional-marketing-mailer'),
                'singular_name' => _x('Statistics', 'Post type singular name', 'conditional-marketing-mailer'),
                'menu_name' => _x('Statistics', 'Admin Menu text', 'conditional-marketing-mailer'),
                'name_admin_bar' => _x('Statistics', 'Add New on Toolbar', 'conditional-marketing-mailer'),
                'add_new' => __('Add New', 'conditional-marketing-mailer'),
                'add_new_item' => __('Add New ', 'conditional-marketing-mailer'),
                'new_item' => __('New ', 'conditional-marketing-mailer'),
                'edit_item' => __('Edit ', 'conditional-marketing-mailer'),
                'view_item' => __('View ', 'conditional-marketing-mailer'),
                'all_items' => __('All ', 'conditional-marketing-mailer'),
                'search_items' => __('Search Statistics', 'conditional-marketing-mailer'),
                'parent_item_colon' => __('Parent Statistics:', 'conditional-marketing-mailer'),
                'not_found' => __('No found.', 'conditional-marketing-mailer'),
                'not_found_in_trash' => __('No found in Trash.', 'conditional-marketing-mailer'),
                'archives' => _x('Statistics archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'conditional-marketing-mailer'),
                'insert_into_item' => _x('Insert into WOO mailer', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'conditional-marketing-mailer'),
                'filter_items_list' => _x('Filter WOO mailer list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'conditional-marketing-mailer'),
                'items_list_navigation' => _x('WOO mailer list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'conditional-marketing-mailer'),
                'items_list' => _x('WOO mailer list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'conditional-marketing-mailer'),
            );

            $args = array(
                'labels' => $labels,
                'public' => false,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'query_var' => true,
                'rewrite' => array('slug' => 'wcmm'),
                'capability_type' => 'page',
                'capabilities' => array(
                    'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
                    'edite_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
                ),
                'map_meta_cap' => true,
                'has_archive' => false,
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title'),

            );

            register_post_type('mail_send', $args);
        }

        public function disable_new_posts()
        {
            if (isset($_GET['post_type']) && $_GET['post_type'] == 'mail_send') {
                ?>
                <style type="text/css">
                    .page-title-action {
                        display: none;
                    }
                </style>
                <?php
            }
            if (isset($_GET['post_type']) && $_GET['post_type'] == 'wcmm') {
                ?>
                <style type="text/css">
                    th.manage-column.column-0.num {
                        text-align: unset !important;
                    }

                </style>
                <?php
            }
        }


        public function filter_cpt_columns($columns)
        {
            $column = array();
            $column['status_mailer'] = __('Status', 'conditional-marketing-mailer');

            array_splice($columns, 2, 0, $column);
            return $columns;
        }

        public function action_custom_columns_content($column_id, $post_id)
        {

            switch ($column_id) {
                case 'status_mailer':
                    echo ('publish' == get_post_status($post_id)) ? 'Active' : 'Not Active';
                    break;
            }
        }

        public function my_custom_bulk_actions($actions, $post)
        {
            if ('wcmm' === $post->post_type) {
                unset($actions['view']);
            }
            if ('mail_send' === $post->post_type) {
                $actions = array();
            }

            return $actions;
        }

    }

    new WCMM_create_cancel_order_post_type();

}

