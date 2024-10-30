<?php
if (  !class_exists( 'WCMM_metaBox' ) ) {

    class WCMM_metaBox
    {
        function __construct()
        {
            add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
            add_action('save_post', array($this, 'save'));
            add_action('wp_ajax_get_products_list', array($this, 'get_products_list'));
            add_action('wp_ajax_nopriv_get_products_list', array($this, 'get_products_list'));
        }

        function register_meta_boxes()
        {
            add_meta_box('Query-balder', __('Condition', 'conditional-marketing-mailer'), array($this, 'display_callback'), 'wcmm');
            add_meta_box('coupon-balder', __('Coupon Options', 'conditional-marketing-mailer'), array($this, 'display_callback_coupon'), 'wcmm');
            add_meta_box('cronjob-balder', __('Sending Options', 'conditional-marketing-mailer'), array($this, 'display_callback_cronjob'), 'wcmm');
            add_meta_box('massage-balder', __('Massage', 'conditional-marketing-mailer'), array($this, 'display_callback_massage'), 'wcmm');
            add_action('admin_print_styles', array($this, 'SUM_hkdc_admin_styles'));
            add_action('admin_enqueue_scripts', array($this, 'SUM_hkdc_admin_scripts'));
            add_action('admin_footer', array($this, 'add_script'));
        }

        public function display_callback_cronjob($post)
        {
            ?>
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th class="width-300 no-border-top"><?php _e('Task Scheduler', 'conditional-marketing-mailer'); ?></th>
                            <th scope="col" class="no-border-top">
                                <?php $cron_job = get_post_meta(get_the_ID(), 'cron_job', true); ?>
                                <select id="cron_job" name="cron_job" class="select short input_custom_style">
                                    <option value="2" <?php selected($cron_job, 2); ?>><?php _e('Wordpress Schedule', 'conditional-marketing-mailer'); ?></option>
                                    <option value="1" <?php selected($cron_job, 1); ?>><?php _e('Server Side Cron Jop', 'conditional-marketing-mailer'); ?></option>
                                </select>
                            </th>
                        </tr>
                        <tr id="server_cron_job">
                            <th class="width-300"><?php _e('Cron Job Link', 'conditional-marketing-mailer'); ?></th>
                            <th scope="col">
                                <?php
                                if (get_post_meta(get_the_ID(), 'hash', true)) {
                                    $hash = get_post_meta(get_the_ID(), 'hash', true);
                                } else {
                                    $hash = md5(rand(100, 100000000));
                                }
                                ?>
                                <input type="hidden" id="hash" name="hash" value="<?php esc_attr_e($hash); ?>">

                                <div> */5 * * * * wget -O
                                    - <?php echo esc_url(get_rest_url('', 'wcmm/send/' . $hash)); ?> >/dev/null 2>&1
                                </div>
                                <div><?php _e('With the frequency of running it every 5 minutes', 'conditional-marketing-mailer'); ?></div>
                                <div><a href="https://documentation.cpanel.net/display/68Docs/Cron+Jobs"
                                        target="_blank"> <?php _e('click here for more details about cron jobs', 'conditional-marketing-mailer'); ?></a>
                                </div>
                            </th>
                        </tr>
                        <tr id="wordpress_cron_job">
                            <th class="width-300"></th>
                            <th scope="col">
                                <div></div>
                            </th>
                        </tr>

                        </tbody>
                    </table>

                </div>
            </div>
            <?php
        }

        public function display_callback_coupon($post)
        {
            ?>
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th class="width-300 no-border-top"><?php _e('Use Coupon', 'conditional-marketing-mailer'); ?></th>
                            <th scope="col" class="no-border-top">
                                <?php $use_coupon = get_post_meta(get_the_ID(), 'use_coupon', true); ?>
                                <select id="use_coupon" name="use_coupon"
                                        class="select short input_custom_style" <?php if (get_option('woocommerce_enable_coupons') != 'yes') { ?> disabled<?php } ?>>
                                    <option value="1" <?php selected($use_coupon, 1); ?>><?php _e('NO', 'conditional-marketing-mailer'); ?></option>
                                    <option value="2" <?php selected($use_coupon, 2); ?>><?php _e('YES', 'conditional-marketing-mailer'); ?></option>
                                </select>
                                <span><?php if (get_option('woocommerce_enable_coupons') != 'yes') {
                                        _e('please Enable the use of coupon codes from woocommerce settings', 'conditional-marketing-mailer');
                                    } ?></span>
                            </th>
                        </tr>

                        </tbody>
                    </table>
                    <table class="table" id="coupon_option">
                        <tbody>
                        <tr>
                            <th class="width-300"><?php _e('Discount Type', 'conditional-marketing-mailer'); ?></th>
                            <th scope="col">
                                <?php $discount_type = get_post_meta(get_the_ID(), 'discount_type', true); ?>
                                <select id="discount_type" name="discount_type" class="select short input_custom_style">
                                    <option value="percent" <?php selected($discount_type, 'percent'); ?>><?php _e('Percentage discount', 'conditional-marketing-mailer'); ?></option>
                                    <option value="fixed_cart" <?php selected($discount_type, 'fixed_cart'); ?>><?php _e('Fixed cart discount', 'conditional-marketing-mailer'); ?></option>
                                </select>
                            </th>
                        </tr>
                        <tr>
                            <th class="width-300"><?php _e('Coupon Amount', 'conditional-marketing-mailer'); ?></th>
                            <th scope="col">
                                <?php $coupon_amount = get_post_meta(get_the_ID(), 'coupon_amount', true); ?>
                                <input type="number" class="short wc_input_price input_custom_style"
                                       name="coupon_amount" id="coupon_amount"
                                       value="<?php esc_attr_e($coupon_amount); ?>" placeholder="0">
                                <span id="pers">%</span>
                            </th>
                        </tr>
                        <tr>
                            <th class="width-300"><?php _e('Coupon Expiry Date After', 'conditional-marketing-mailer'); ?></th>
                            <th scope="col">
                                <?php $expiry_date = get_post_meta(get_the_ID(), 'expiry_date', true); ?>
                                <input type="number" class="input_custom_style" name="expiry_date" id="expiry_date"
                                       value="<?php esc_attr_e($expiry_date); ?>"
                                       placeholder="5"> <?php _e('DAYS', 'conditional-marketing-mailer'); ?>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        }
		
		
		/**
     * Uses WP's wp_kses to clear some of the html tags but allow some attribs
     * usage: orbisius_html_util::strip_tags($str);
     * uses WordPress' wp_kses()
     * @param str $buffer string buffer
     * @return str cleaned up text
     */
   /**
     * Uses WP's wp_kses to clear some of the html tags but allow some attribs
     * usage: orbisius_html_util::strip_tags($str);
     * uses WordPress' wp_kses()
     * @param str $buffer string buffer
     * @return str cleaned up text
     */
    public static function WCMM_strip_tags($buffer) {
        
		static $default_attribs = array(
            'id' => array(),
            'class' => array(),
            'multiple' => array(),
            'colspan' => array(),
            'title' => array(),
            'style' => array(),
            'data' => array(),
            'data-mce-id' => array(),
            'data-value' => array(),
            'data-chart' => array(),
            'data-mce-style' => array(),
            'data-maxy' => array(),
            'data-pageviews' => array(),
            'data-visitors' => array(),
            'data-newvisitor' => array(),
            'data-colors' => array(),
            'data-totalpageviews' => array(),
            'data-tdays' => array(),
            'data-graph' => array(),
            'data-referrak_param' => array(),
            'data-firsttimevisitors' => array(),
			'onclick' => array(),
			'aria-describedby'  => array(),
			'name' => array(),
			'id' => array(),
			'value' => array(),
			'selected' => array(),
			'checkbox' => array(),
			'checked' => array(),
			'scope'  => array(),
			'for'  => array(),
			'multiple'  => array(),
			'type'  => array(),
			'method'  => array(),
			'ipaddress'  => array(),
			'row'  => array(),
			'data-id'  => array(),
			'data-ipaddress'  => array()

        );

        $allowed_tags = array(
            'select'           => $default_attribs,
            'checkbox'           => $default_attribs,
            'input'           => $default_attribs,
            'form'           => $default_attribs,
            'option'           => $default_attribs,
            'value'           => $default_attribs,
            'optgroup'           => $default_attribs,
            'label'           => $default_attribs,
            'div'           => $default_attribs,
            'table'             => array_merge( $default_attribs, array(
                'style' => array(),
                'method' => array(),
            ) ),
            'tr'           => $default_attribs,
            'h2'           => $default_attribs,
            'h1'           => $default_attribs,
            'h3'           => $default_attribs,
            'b'           => $default_attribs,
            'th'           => $default_attribs,
            'thead'           => $default_attribs,
            'script'           => $default_attribs,
            'tbody'           => $default_attribs,
            'tfooter'           => $default_attribs,
            'td'           => $default_attribs,
            'span'          => $default_attribs,
            'p'             => $default_attribs,
            'a'             => array_merge( $default_attribs, array(
                'href' => array(),
                'target' => array('_blank', '_top'),
            ) ),
            'u'             =>  $default_attribs,
            'i'             =>  $default_attribs,
            'q'             =>  $default_attribs,
            'b'             =>  $default_attribs,
            'ul'            => $default_attribs,
            'ol'            => $default_attribs,
            'li'            => $default_attribs,
            'br'            => $default_attribs,
            'hr'            => $default_attribs,
            'strong'        => $default_attribs,
            'blockquote'    => $default_attribs,
            'del'           => $default_attribs,
            'strike'        => $default_attribs,
            'em'            => $default_attribs,
            'code'          => $default_attribs,
        );

        if (function_exists('wp_kses')) { // WP is here
		
            $buffer = wp_kses($buffer, $allowed_tags);
        } else {
            $tags = array();

            foreach (array_keys($allowed_tags) as $tag) {
                $tags[] = "<$tag>";
            }

            $buffer = wsm_strip_tags($buffer, join('', $tags));
        }

        $buffer = trim($buffer);

        return $buffer;
    }
	

        public function save($post_id)
        {

            if (!isset($_POST['conditional-marketing-mailer_nonce'])) {
                return $post_id;
            }
            if (!wp_verify_nonce($_POST['conditional-marketing-mailer_nonce'], 'conditional-marketing-mailer')) {
                return $post_id;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // Check the user's permissions.
			$WCMM_post_type = sanitize_text_field($_POST['post_type']);
			
            if ('wcmm' == $WCMM_post_type) {
                if (!current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
            } else {
                return true;
            }

            $type = array();
            if (isset($_POST['type']) && !empty($_POST['type'])) {
                $type = $this->sanitize_text_or_array_field(array_filter($_POST['type']));
            }
            update_post_meta($post_id, 'type', $type);

            $category_select = array();
            if (isset($_POST['category_select']) && !empty($_POST['category_select'])) {
                $category_select = $this->sanitize_text_or_array_field(array_filter($_POST['category_select']));
            }
            update_post_meta($post_id, 'category_select', $category_select);

            $tags_select = array();
            if (isset($_POST['tags_select']) && !empty($_POST['tags_select'])) {
                $tags_select = $this->sanitize_text_or_array_field($_POST['tags_select']);
            }
            update_post_meta($post_id, 'tags_select', $tags_select);

            $products = array();
            if (isset($_POST['products']) && !empty($_POST['products'])) {
                $products = $this->sanitize_text_or_array_field($_POST['products']);
            }
            update_post_meta($post_id, 'products', $products);

            $statuses = array();
            if (isset($_POST['statuses']) && !empty($_POST['statuses'])) {
                $statuses = $this->sanitize_text_or_array_field($_POST['statuses']);
            }
            update_post_meta($post_id, 'statuses', $statuses);

            $relation = array();
            if (isset($_POST['relation']) && !empty($_POST['relation'])) {
                $relation = $this->sanitize_text_or_array_field($_POST['relation']);
            }
            update_post_meta($post_id, 'relation', $relation);


            update_post_meta($post_id, 'hash', $this->sanitize_text_or_array_field($_POST['hash']));


            if (isset($_POST['use_coupon']) && !empty($_POST['use_coupon'])) {
                update_post_meta($post_id, 'use_coupon', $this->sanitize_text_or_array_field($_POST['use_coupon']));
            }
            if (isset($_POST['discount_type']) && !empty($_POST['discount_type'])) {
                update_post_meta($post_id, 'discount_type', $this->sanitize_text_or_array_field($_POST['discount_type']));
            }
            if (isset($_POST['coupon_amount']) && !empty($_POST['coupon_amount'])) {
                update_post_meta($post_id, 'coupon_amount', $this->sanitize_text_or_array_field($_POST['coupon_amount']));
            }
            if (isset($_POST['expiry_date']) && !empty($_POST['expiry_date'])) {
                update_post_meta($post_id, 'expiry_date', $this->sanitize_text_or_array_field($_POST['expiry_date']));
            }
            if (isset($_POST['massage_title']) && !empty($_POST['massage_title'])) {
                update_post_meta($post_id, 'massage_title', $this->sanitize_text_or_array_field($_POST['massage_title']));
            }
            if (isset($_POST['cron_job']) && !empty($_POST['cron_job'])) {
                update_post_meta($post_id, 'cron_job', $this->sanitize_text_or_array_field($_POST['cron_job']));
            }

            $massage = $this->WCMM_strip_tags($_POST['massage']);
            update_post_meta($post_id, 'massage_title', $this->sanitize_text_or_array_field($_POST['massage_title']));
            update_post_meta($post_id, 'massage', $massage);
        }

        public function sanitize_text_or_array_field($array_or_string)
        {
            if (is_string($array_or_string)) {
                $array_or_string = sanitize_text_field($array_or_string);
            } elseif (is_array($array_or_string)) {
                foreach ($array_or_string as $key => &$value) {
                    if (is_array($value)) {
                        $value = $this->sanitize_text_or_array_field($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                }
            }
            return $array_or_string;
        }

        public function get_products_list()
        {
            if (!isset($_POST['searchTerm'])) {
                $query = new WC_Product_Query(array(
                    'limit' => 5,
                ));

            } else {
                $search = sanitize_text_field($_POST['searchTerm']);
                $query = new WC_Product_Query(array(
                    'limit' => 5,
                    's' => $search
                ));
            }
            $products = $query->get_products();
            $data = array();
            foreach ($products as $row) {
                $data[] = array("id" => $row->get_id(), "text" => $row->get_name());
            }
            echo json_encode($data);
            exit;
        }

        public function display_callback($post)
        {
            wp_nonce_field('conditional-marketing-mailer', 'conditional-marketing-mailer_nonce');
            ?>
            <div class="form-group">
                <div class="table-responsive">
                    <table class="table " id="dynamic_field">
                        <?php
                        $types = get_post_meta($post->ID, 'type', true);
                        $num = 1;
                        if (!empty($types)) {
                            foreach ($types as $key => $type) {
                                ?>
                                <tr id="row<?php esc_attr_e($num); ?>">
                                    <td style="width: 25%;" class="<?php echo $num == 1 ? "no-border-top" : ''; ?>">

                                        <select name="type[]" class="form-control name_list " style="height:38px">
                                            <option value="" <?php selected($type, ''); ?>><?php _e('...', 'conditional-marketing-mailer'); ?></option>
                                            <option value="Category" <?php selected($type, 'Category'); ?>><?php _e('Category', 'conditional-marketing-mailer'); ?></option>
                                            <option value="Tag" <?php selected($type, 'Tag'); ?>><?php _e('Tag', 'conditional-marketing-mailer'); ?></option>
                                            <option value="Product_in_order" <?php selected($type, 'Product_in_order'); ?>><?php _e('Product in order', 'conditional-marketing-mailer'); ?></option>
                                            <option value="statuses" <?php selected($type, 'statuses'); ?>><?php _e('Order Status', 'conditional-marketing-mailer'); ?></option>
                                        </select>

                                    </td>
                                    <td style="width: 50%;" class="<?php echo $num == 1 ? "no-border-top" : ''; ?>">
                                        <?php $this->get_input_value($type); ?>
                                    </td>
                                    <td style="width: 18%;" class="<?php echo $num == 1 ? "no-border-top" : ''; ?>">
                                        <?php if ($type != 'statuses') { ?>
                                            <?php $relation = get_post_meta($post->ID, 'relation', true); ?>
                                            <select name="relation[<?php esc_attr_e($type); ?>]" class="form-control ">
                                                <option value="OR" <?php selected($relation[$type], 'OR'); ?>><?php _e('OR', 'conditional-marketing-mailer'); ?></option>
                                                <option value="AND" <?php selected($relation[$type], 'AND'); ?>><?php _e('AND', 'conditional-marketing-mailer'); ?></option>
                                            </select>
                                        <?php } ?>
                                    </td>
                                    <td class="close_btn_row no-border-top">
                                        <button type="button" name="remove" id="<?php esc_attr_e($num); ?>"
                                                class="btn btn-danger btn_remove ">X
                                        </button>
                                    </td>
                                </tr>
                                <?php
                                $num++;
                            }
                        }
                        ?>
                        <tr>
                            <td style="width:25%">

                                <select name="type[]" class="form-control name_list "
                                        style="max-width: 300px;height:38px">
                                    <option value="">...</option>
                                    <option value="Category">Category</option>
                                    <option value="Tag">Tag</option>
                                    <option value="Product_in_order">Product in order</option>
                                    <option value="statuses">Order Status</option>
                                </select>
                            </td>
                            <td style="width:50%"></td>
                            <td style="width:18%"></td>
                            <td></td>
                        </tr>
                    </table>
                    <button type="button" name="add" id="add" class="btn btn-success">Add More</button>
                </div>
            </div>
            <?php
        }

        public function get_input_value($val)
        {
            if ($val == 'Category') {
                $meta = (array)get_post_meta(get_the_ID(), 'category_select', true);
                $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => 0));

                ?>
                <select name="category_select[]" class="form-control category_list " multiple="multiple"
                        style="width:100%">
                    <?php foreach ($product_categories as $row) { ?>
                        <option value="<?php esc_attr_e($row->term_id); ?>" <?php selected($row->term_id, !empty($meta) ? $meta[array_search($row->term_id, ($meta))] : ''); ?>><?php esc_html_e($row->name); ?></option>
                    <?php } ?>
                </select>

            <?php } else if ($val == 'Tag') {
                $meta = (array)get_post_meta(get_the_ID(), 'tags_select', true);
                $product_categories = get_terms(array('taxonomy' => 'product_tag', 'hide_empty' => 0)); ?>
                <select name="tags_select[]" class="form-control tag_list " multiple="multiple" style="width:100%">
                    <?php foreach ($product_categories as $row) { ?>
                        <option value="<?php esc_attr_e($row->term_id); ?>" <?php selected($row->term_id, !empty($meta) ? $meta[array_search($row->term_id, ($meta))] : ''); ?>><?php esc_html_e($row->name); ?></option>
                    <?php } ?>
                </select>


            <?php } else if ($val == 'Product_in_order') {
                $meta = get_post_meta(get_the_ID(), 'products', true);
                ?>
                <select name="products[]" class="form-control products_list " multiple="multiple" style="width:100%">
                    <?php foreach ($meta as $row) { ?>
                        <option value="<?php esc_attr_e($row); ?>"
                                selected><?php esc_html_e(get_the_title($row)); ?></option>
                    <?php } ?>
                </select>
                <?php
            } else if ($val == 'statuses') {
                $meta = (array)get_post_meta(get_the_ID(), 'statuses', true);
                $order_statuses = wc_get_order_statuses(); ?>
                <select name="statuses[]" class="form-control statuses_list " multiple="multiple" style="width:100%">
                    <?php foreach ($order_statuses as $key => $val) { ?>
                        <option value="<?php esc_attr_e($key); ?>" <?php selected($key, !empty($meta) ? $meta[array_search($key, ($meta))] : ''); ?>><?php esc_html_e($val); ?></option>
                    <?php } ?>
                </select>
                <?php
            }
        }

        public function display_callback_massage($post)
        {

            ?>
            
            <table class="table" id="coupon_option">
                <tbody>
                <tr>
                    <th class="width-300 no-border-top"><?php _e('Massage Title', 'conditional-marketing-mailer'); ?></th>
                    <th scope="col" class="no-border-top">
                        <?php $massage_title = get_post_meta(get_the_ID(), 'massage_title', true);

								if(empty($massage_title))
								{
									$massage_title = 'complete your order and save 50%';
								}
						?>
                        <input type="text" required class="short wc_input_price input_custom_style" name="massage_title"
                               id="massage_title" style="width:80%" value="<?php esc_attr_e($massage_title); ?>" placeholder="">
                    </th>
                </tr>
                </tbody>
            </table>


            <i class="short_code_style">
                {{customer_first_name}} , {{customer_last_name}} , {{order_id}} , {{order_products}} , {{order_date}} ,
                {{order_total}} , {{coupon_code}} , {{cart_url}} , {{shop_url}} , {{website_URL}} , {{website_title}} ,
                {{website_email}}
            </i>
			
			

            <?php
            $massage = get_post_meta($post->ID, 'massage', true);
            if ($massage == '') {
                $massage = '
			<div align="center">
<table style="border: 1px solid rgba(0,0,0,.1); border-radius: 5px !important; border-collapse: separate;" width="88%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background-color: #00a8ff;" align="center" valign="middle">
<h2 style="padding:5px;"><span style="color: #ffffff;">Welcome {{customer_first_name}} , {{customer_last_name}}</span></h2>
</td>
</tr>
<tr>
<td align="center" valign="top">
<table border="0" width="100%" cellspacing="0" cellpadding="20">
<tbody>
<tr>
<td valign="top" height="287">
<p style="text-align: left;">First of all, thank you for choosing our products. We really appreciate this.</p>
<p style="text-align: left;">Secondly,</p>
<p style="text-align: left;">You’ve received the following email from us because you made or try to make this list of orders.</p>
<p style="text-align: left;">{{order_products}}</p>
<p style="text-align: left;">We really want to see you again,</p>
<p style="text-align: left;">You can use this Coupon Code: <b>{{coupon_code}}</b> for your future orders with us.</p>
<p style="text-align: left;">Please visit our website for more details: {{website_URL}}.</p>
</td>
</tr>
<tr>
<td>
<p style="text-align: center;">Contact Us: {{website_email}}</p>
<p style="text-align: center;">{{website_title}}</p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
            ';
            }
            wp_editor($massage, 'massage');
			echo '<br><br><button class="btn btn-success" href="#" id="click_to_send_email_test">Send a test message to the site admin</button>';
			

        }

        public function add_script()
        {
            global $post_type;


            if ('wcmm' == $post_type) {
                ?>
                <script>
                    "use strict";
                    jQuery(document).ready(function ($) {
                        "use strict";
                        <?php
                        $count_of_colom = count((array)get_post_meta(get_the_ID(), 'type', true));
                        if ($count_of_colom <= 0 || $count_of_colom == '0' || $count_of_colom == '') {
                            $count_of_colom = 1;
                        }
                        ?>
                        var i =<?php echo esc_js($count_of_colom);?>;
                        $('#add').click(function () {
                            i++;
                            $('#dynamic_field').append(
                                '<tr id="row' + i + '"><td>' +
                                '<select name="type[]" class="form-control name_list " style="height:38px">\n' +
                                '        <option value=""><?php _e("...", "conditional-marketing-mailer");?></option>\n' +
                                '        <option value="Category"><?php _e("Category", "conditional-marketing-mailer");?></option>\n' +
                                '        <option value="Tag"><?php _e("Tag", "conditional-marketing-mailer");?></option>\n' +
                                '        <option value="Product_in_order"><?php _e("Product in order", "conditional-marketing-mailer");?></option>\n' +
                                '        <option value="statuses"><?php _e("Order Status", "conditional-marketing-mailer");?></option>\n' +
                                '</select>' +
                                '</td><td></td><td></td><td class="close_btn_row"><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>'
                            );
                        });

                        $(document).on('click', '.btn_remove', function () {
                            var button_id = $(this).attr("id");
                            $('#row' + button_id + '').remove();
                        });
                        $(document).on('change', '.name_list', function () {

                            var selectValue = $(this).val();
                            if (selectValue == 'Category') {
                                $(this).parent().next().html(
                                    <?php $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => 0));?>
                                    '<select name="category_select[]" class="form-control category_list " multiple="multiple">\n' +
                                    <?php foreach($product_categories as $row){?>
                                    '        <option value="<?php echo esc_js($row->term_id); ?>" ><?php echo esc_js($row->name); ?></option>\n' +
                                    <?php } ?>
                                    '</select>'
                                );
                                $(this).parent().next().next().html(
                                    '<select name="relation[Category]" class="form-control ">\n' +
                                    '     <option value="OR" ><?php _e("OR", "conditional-marketing-mailer");?></option>\n' +
                                    '     <option value="AND" ><?php _e("AND", "conditional-marketing-mailer");?></option>\n' +
                                    '</select>'
                                );
                            } else if (selectValue == 'Tag') {
                                $(this).parent().next().html(
                                    <?php $product_categories = get_terms(array('taxonomy' => 'product_tag', 'hide_empty' => 0));?>
                                    '<select name="tags_select[]" class="form-control tag_list " multiple="multiple">\n' +
                                    <?php foreach($product_categories as $row){?>
                                    '        <option value="<?php echo esc_js($row->term_id); ?>"><?php echo esc_js($row->name); ?></option>\n' +
                                    <?php } ?>
                                    '</select>'
                                );
                                $(this).parent().next().next().html(
                                    '<select name="relation[Tag]" class="form-control ">\n' +
                                    '     <option value="OR" ><?php _e("OR", "conditional-marketing-mailer");?></option>\n' +
                                    '     <option value="AND" ><?php _e("AND", "conditional-marketing-mailer");?></option>\n' +
                                    '</select>'
                                );

                            } else if (selectValue == 'Product_in_order') {
                                $(this).parent().next().html(
                                    '<select name="products[]" class="form-control products_list " multiple="multiple">\n' +

                                    '</select>'
                                );
                                $(this).parent().next().next().html(
                                    '<select name="relation[Product_in_order]" class="form-control ">\n' +
                                    '     <option value="OR" ><?php _e("OR", "conditional-marketing-mailer");?></option>\n' +
                                    '     <option value="AND" ><?php _e("AND", "conditional-marketing-mailer");?></option>\n' +
                                    '</select>'
                                );

                            } else if (selectValue == 'statuses') {

                                $(this).parent().next().html(
                                    <?php $order_statuses = wc_get_order_statuses();?>
                                    '<select name="statuses[]" class="form-control statuses_list " multiple="multiple">\n' +
                                    <?php foreach($order_statuses as $key=>$val){?>
                                    '        <option value="<?php echo esc_js($key); ?>"><?php echo esc_js($val); ?></option>\n' +
                                    <?php } ?>
                                    '</select>'
                                );
                                $(this).parent().next().next().html("");
                            }
                            select_init();
                        });

                        function select_init() {
                            jQuery('.tag_list').select2();
                            jQuery('.category_list').select2();
                            jQuery('.statuses_list').select2();
                            $(".products_list").select2({
                                ajax: {
                                    url: "<?php echo esc_js(admin_url('admin-ajax.php')); ?>",
                                    type: "post",

                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            searchTerm: params.term,
                                            action: 'get_products_list'
                                        };
                                    },
                                    processResults: function (response) {
                                        return {
                                            results: response
                                        };
                                    },
                                    cache: true
                                }
                            });
                        }

                        select_init();
                        jQuery(document).ready(function () {
                            jQuery(".name_list").select2();
                        });
                        if (jQuery("#use_coupon").val() == 1) {
                            jQuery('#coupon_option').hide();
                        } else {
                            jQuery('#coupon_option').show();
                        }
                        $('#use_coupon').on('change', function (e) {
                            if (this.value == 1) {
                                jQuery('#coupon_option').hide();
                            } else {
                                jQuery('#coupon_option').show();
                            }
                        });
                        $(document).on('change', '#cron_job', function () {
                            var cronjob_id = $(this).val();
                            if (cronjob_id == 1) {
                                jQuery("#server_cron_job").show();
                                jQuery("#wordpress_cron_job").hide();
                            } else {
                                jQuery("#wordpress_cron_job").show();
                                jQuery("#server_cron_job").hide();
                            }

                        });
                        var cronjob_id = $('#cron_job').val();
                        if (cronjob_id == 1) {
                            jQuery("#server_cron_job").show();
                            jQuery("#wordpress_cron_job").hide();
                        } else {
                            jQuery("#wordpress_cron_job").show();
                            jQuery("#server_cron_job").hide();
                        }
                        jQuery(document).on('change', '#discount_type', function () {
                            var discount_type = jQuery(this).val();
                            if (discount_type == "percent") {
                                jQuery("#pers").show();
                            } else {
                                jQuery("#pers").hide();
                            }
                        });
                        var discount_type = jQuery('#discount_type').val();
                        if (discount_type == "percent") {
                            jQuery("#pers").show();
                        } else {
                            jQuery("#pers").hide();
                        }
                    });
                </script>

            <?php }
        }

        function SUM_hkdc_admin_styles($page)
        {
            global $post_type;
            if ('wcmm' == $post_type) {
                wp_enqueue_style('bootstrap', WCMM_PLUGIN_URL . '/assets/css/bootstrap.css');
                wp_enqueue_style('select2-css', WCMM_PLUGIN_URL . '/assets/css/select2.css?v=3.6.8');
				
				$v = time();
				wp_enqueue_style('wcmm_admin_css', WCMM_PLUGIN_URL . '/assets/css/custom.css?v='.$v);

            }
        }

        function SUM_hkdc_admin_scripts($page)
        {
            global $post_type;
            if ('wcmm' == $post_type) {
                wp_enqueue_script('tail-datetime', WCMM_PLUGIN_URL . '/assets/js/bootstrap.min.js?asd', array('jquery'), '4.0', true);
                wp_enqueue_script('select2', WCMM_PLUGIN_URL . '/assets/js/select2.full.min.js', array('jquery'), '4.0', true);
                wp_enqueue_script('ajax-script', plugins_url('/../assets/js/custom.js?v=1234', __FILE__), array('jquery'));

                wp_localize_script('ajax-script', 'ajax_object',
                    array('ajax_url' => admin_url('admin-ajax.php')));
            }
        }
    }

    new WCMM_metaBox();

}
