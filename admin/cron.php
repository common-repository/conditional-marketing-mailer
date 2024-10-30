<?php
if (  !class_exists( 'WCMM_cron_cansel' ) ) {

    class WCMM_cron_cansel
    {
        public $query_id;

        public function __construct()
        {
            add_action('rest_api_init', array($this, 'prefix_register_book_route'));
            add_action('my_hourly_event', array($this, 'do_this_hourly'));
            add_action('wp_footer', array($this, 'do_this_hourly'));
            add_action('wp', array($this, 'my_activation'));
        }

        public function prefix_register_book_route()
        {
            register_rest_route('wcmm', '/send/(?P<id>[a-zA-Z0-9-]+)', array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'prefix_get_book'),
            ));
            register_rest_route('wcmm', '/massage_open/(?P<id>\d+)', array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'massage_open'),
            ));
        }


        public function my_activation()
        {
            if (!wp_next_scheduled('my_hourly_event')) {
                wp_schedule_event(time() + 5 * 60, 'hourly', 'my_hourly_event');
            }
        }

        public function do_this_hourly()
        {
            $posts = get_posts(array(
                'numberposts' => 1,
                'post_type' => 'wcmm',
                'meta_key' => 'cron_job',
                'meta_value' => 2
            ));

            if (!empty($posts)) {
                foreach ($posts as $post) {
                    $hash = array();
                    $hash['id'] = esc_html(get_post_meta($post->ID, 'hash', true));
                    $this->prefix_get_book($hash);
                }


            }
        }

        public function massage_open($request)
        {
            $id_m_open = absint(intval($request['id']));
            $rd_args = array(
                'post_type' => 'mail_send',
                'meta_key' => 'rand_int',
                'meta_value' => absint($id_m_open)
            );

            $rd_query = new WP_Query($rd_args);
            if ($rd_query->have_posts()) :
                while ($rd_query->have_posts()) : $rd_query->the_post();
                    update_post_meta(get_the_ID(), 'opened', 1);
                endwhile;
            endif;

            wp_reset_query();
        }

        public function prefix_get_book($request)
        {
            $posts = get_posts(array(
                'numberposts' => 1,
                'post_type' => 'wcmm',
                'meta_key' => 'hash',
                'meta_value' => sanitize_text_field($request['id'])
            ));

            if (isset($posts['0']->ID)) {
                $this->query_id = absint($posts['0']->ID);
            } else {
                return array(array('status' => false));
            }


            $q = new WCMM_query_builder($this->query_id);
            $query = $q->query();

            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $order_id = get_the_ID();
                    $order = new WC_Order($order_id);
                    $to = $order->get_billing_email();
                    $massage_title = get_post_meta(absint($this->query_id), 'massage_title', true);
                    $massage = $this->massage($order, $order_id);
                    $rand_int = rand();
                    $image_opened = sprintf('<img src="%s" width="0" height="0">', get_rest_url('', 'wcmm/massage_open/' . $rand_int));
                    $massage_to_send = $massage . $image_opened;


                    add_filter('wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type'));
                    if (wp_mail($to, $massage_title, $massage_to_send)) {
                        add_post_meta($this->query_id, 'order_id_send', $order_id);
                        $my_post = array(
                            'post_title' => '#' . absint($order_id) . ' ' . $to,
                            'post_type' => 'mail_send',
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_author' => 1,
                        );

                        $mail_id = wp_insert_post($my_post);

                        update_post_meta($mail_id, 'query_id', absint($this->query_id));
                        update_post_meta($mail_id, 'order_id', absint($order_id));
                        update_post_meta($mail_id, 'to', sanitize_email($to));
                        update_post_meta($mail_id, 'massage_title', sanitize_title($massage_title));
                        update_post_meta($mail_id, 'massage', $massage);
                        update_post_meta($mail_id, 'rand_int', absint($rand_int));
                    }
                    remove_filter('wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type'));
                endwhile;
            } else {
                return array(array('status' => false));
            }

            wp_reset_query();

            return array(array('status' => true));
        }

        public function wpdocs_set_html_mail_content_type()
        {
            return 'text/html';
        }

        public function massage($order = array(), $order_id = 0)
        {
            $metaMassage = get_post_meta($this->query_id, 'massage', true);
            $massagMap = array();
            $massagMap['{{customer_first_name}}'] = $order->get_billing_first_name();
            $massagMap['{{customer_last_name}}'] = $order->get_billing_last_name();
            $massagMap['{{order_id}}'] = absint($order_id);
            $product = '<table border="1" cellspacing="0px" cellpadding="3" style="max-width:768px; width:100%; border: 1px solid black; border-collapse: collapse; ">';
            $product .= '<tbody>';
            $product .= '
                        <tr style="background-color:#f9f9f9; text-align:left">
                            <th  width="500px">' . __("Product title", "conditional-marketing-mailer") . '</th>
                            <th>' . __("Quantity", "conditional-marketing-mailer") . '</th>
                            <th>' . __("Price", "conditional-marketing-mailer") . '</th>
                        </tr>
                        ';
            foreach ($order->get_items() as $item_id => $item) {
                $product .= '
                         <tr style="text-align:left">
                            <th>' . $item->get_name() . '</th>
                            <th>' . $item->get_quantity() . '</th>
                            <th>' . $item->get_total() . '</th>
                        </tr>
                        ';
            }
            $product .= '<tr style="text-align:left">
                            
                            <th colspan="2"></th>
                            <th>' . $order->get_formatted_order_total() . '</th>
                        </tr>';
            $product .= '</tbody>';
            $product .= '</table>';


            $massagMap['{{order_products}}'] = $product;
            $massagMap['{{order_date}}'] = $order->get_date_created()->format('Y F j, g:i a');
            $massagMap['{{order_total}}'] = $order->get_formatted_order_total();


            $massagMap['{{website_URL}}'] = home_url();
            $massagMap['{{cart_url}}'] = wc_get_cart_url();
            $massagMap['{{shop_url}}'] = get_permalink(woocommerce_get_page_id('shop'));
            $massagMap['{{website_title}}'] = get_bloginfo('name');
            $massagMap['{{website_email}}'] = get_bloginfo('admin_email');

            foreach ($massagMap as $key => $val) {
                $metaMassage = str_replace($key, $val, $metaMassage);
            }

            if (strstr($metaMassage, '{{coupon_code}}')) {

                $coupon_code = $this->generateRandomStringCouponTitle(15);
                $amount = get_post_meta($this->query_id, 'coupon_amount', true) ? get_post_meta($this->query_id, 'coupon_amount', true) : 0; // Amount
                $discount_type = get_post_meta($this->query_id, 'discount_type', true); // Type: fixed_cart, percent, fixed_product, percent_product
                $expiry_date = get_post_meta($this->query_id, 'expiry_date', true) ? get_post_meta($this->query_id, 'expiry_date', true) : 0;
                $expiry_date = date("Y-m-d", strtotime("+ " . $expiry_date . " day"));
                $coupon = array(
                    'post_title' => sanitize_title($coupon_code),
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'shop_coupon'
                );

                $new_coupon_id = wp_insert_post($coupon);
                update_post_meta($new_coupon_id, 'discount_type', $discount_type);
                update_post_meta($new_coupon_id, 'coupon_amount', $amount);
                update_post_meta($new_coupon_id, 'individual_use', 'no');
                update_post_meta($new_coupon_id, 'product_ids', '');
                update_post_meta($new_coupon_id, 'exclude_product_ids', '');
                update_post_meta($new_coupon_id, 'usage_limit', '');
                update_post_meta($new_coupon_id, 'expiry_date', $expiry_date);
                update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
                update_post_meta($new_coupon_id, 'free_shipping', 'no');

                $metaMassage = str_replace('{{coupon_code}}', $coupon_code, $metaMassage);
            }

            return $metaMassage;

        }

        public function generateRandomStringCouponTitle($length = 15)
        {
            return substr(str_shuffle(str_repeat($x = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
        }

    }

    new WCMM_cron_cansel();


}
