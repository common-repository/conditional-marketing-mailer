<?php
if( ! class_exists( 'WCMM_testing_massage' ) ) {

    class WCMM_testing_massage
    {

        public function __construct()
        {

            add_action('wp_ajax_send_email', array($this, 'send_email'));

            add_action('wp_ajax_nopriv_send_email', array($this, 'send_email'));

        }
		
				
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
	
	
        public function send_email()
        {


            $massage_title = sanitize_title($_POST['title']);

            $massage_massage = $this->WCMM_strip_tags($_POST['massage']);

            $massage = $this->massage($massage_massage);


            $to = get_bloginfo('admin_email');

            add_filter('wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type'));

            add_filter('wp_mail_content_type', function ($content_type) {

                return 'text/html';

            });

            if (wp_mail($to, $massage_title, $massage)) {

                $res = array('status' => true, 'massage' => __('the massage has been sent to: ', 'conditional-marketing-mailer'), 'email' => $to);

                print_r(json_encode($res));

                exit;

            }

            remove_filter('wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type'));

            $res = array('status' => true, 'massage' => __('ops! The testing message can\'t be sent to: ', 'conditional-marketing-mailer'), 'email' => $to);

            print_r(json_encode($res));

            exit;


            wp_die();

        }

        public function wpdocs_set_html_mail_content_type()
        {

            return 'text/html';

        }

        public function massage($massage = '')
        {

            $metaMassage = $massage;

            $massagMap = array();

            $massagMap['{{customer_first_name}}'] = 'customer first name';

            $massagMap['{{customer_last_name}}'] = 'customer last name';

            $massagMap['{{order_id}}'] = 12;

            $product = '<table border="1" cellspacing="0px" cellpadding="3" style="max-width:768px; width:100%; border: 1px solid black; border-collapse: collapse; ">

					<tbody>

                        <tr style="background-color:#f9f9f9; text-align:left">

                            <th  width="500px">' . __("Product title", "conditional-marketing-mailer") . '</th>

                            <th>' . __("Quantity", "conditional-marketing-mailer") . '</th>

                            <th>' . __("Price", "conditional-marketing-mailer") . '</th>

                        </tr>



                         <tr style="text-align:left">

                            <th>my product title</th>

                            <th>1</th>

                            <th>22.5</th>

                        </tr>

                       <tr style="text-align:left">

                            

                            <th colspan="2"></th>

                            <th>22.5</th>

                        </tr>

					</tbody>

				</table>';


            $massagMap['{{order_products}}'] = $product;

            $massagMap['{{order_date}}'] = date('Y F j, g:i a');

            $massagMap['{{order_total}}'] = '22.5';


            $massagMap['{{website_URL}}'] = home_url();

            $massagMap['{{cart_url}}'] = wc_get_cart_url();

            $massagMap['{{shop_url}}'] = get_permalink(woocommerce_get_page_id('shop'));

            $massagMap['{{website_title}}'] = get_bloginfo('name');

            $massagMap['{{website_email}}'] = get_bloginfo('admin_email');


            foreach ($massagMap as $key => $val) {

                $metaMassage = str_replace($key, $val, $metaMassage);

            }


            if (strstr($metaMassage, '{{coupon_code}}')) {

                $metaMassage = str_replace('{{coupon_code}}', 'trbwsBSd54D', $metaMassage);

            }


            return $metaMassage;


        }


    }

    new WCMM_testing_massage();
}