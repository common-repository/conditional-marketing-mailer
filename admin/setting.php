<?php
if( ! class_exists( 'WCMM_admin_setting' ) ) {
	class WCMM_admin_setting{

		public function __construct() {
			add_action( 'admin_menu', array($this,'WPLFLA_options_page') );
			add_action( 'admin_init', array($this,'WPLFLA_settings_init') );
		}
		public function WPLFLA_settings_init() {
			register_setting( 'WPLFLA', 'WPLFLA_options');
			add_settings_section(
				'WCMM_section_developers',
				'',
				'',
				'WPLFLA'
			);
			add_settings_field(
				'WPLFLA_status',
				__( 'Login Status', 'conditional-marketing-mailer' ),
				array($this,'WPLFLA_field_type_checkbox'),
				'WPLFLA',
				'WCMM_section_developers',
				[
					'label_for' => 'WPLFLA_status',
					'class' => 'WPLFLA_row',
					'WPLFLA_custom_data' => 'custom',
					'desc' => 'enable or disable all failed login attempts using this option'
				]
			);
		}

		public function WCMM_field_type_checkbox( $args ) {
			$options = get_option( 'WCMM_options' );

			?>
			<span class="on_off "><?php esc_html_e( 'OFF', 'conditional-marketing-mailer' ); ?></span>
			<label class="switch">
				<input type="checkbox" value="1" id="<?php esc_attr_e( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['WCMM_custom_data'] ); ?>" name="WCMM_options[<?php echo esc_attr( $args['label_for'] ); ?>]"  <?php echo isset( $options[ $args['label_for']] ) ? ( checked( $options[ $args['label_for'] ], '1', false ) ) : ( '' ); ?>>
				<span class="slider round"></span>
			</label>
			<span class="on_off "><?php esc_html_e( 'ON', 'conditional-marketing-mailer' ); ?></span>
			<p class="description">
				<?php esc_html_e( $args['desc'], 'conditional-marketing-mailer' ); ?>
			</p>

			<?php
		}


		public function WCMM_options_page() {
			 add_submenu_page( 'edit.php?post_type=wcmm', __('option','conditional-marketing-mailer'), __('option','conditional-marketing-mailer'),'manage_options', 'WCMMLOG',array($this,"WCMM_options_page_html"));
		}
		public function clear_log(){
			if(update_option( 'WCMM_login_failed', array() )){
				$this->update_notice();
				return true;
			}else{
				$this->error_notice();
				return false;
			}
		}
		public function clear_log_block_id(){
			if(update_option( 'WCMM_block_ip', array() )){
				$this->update_notice();
				return true;
			}else{
				$this->error_notice();
				return false;
			}
		}
		function update_notice() {
			?>
			<div class="updated notice">
				<p><?php _e( 'The operation completed successfully.', 'conditional-marketing-mailer' ); ?></p>
			</div>
			<?php
		}
		function error_notice() {
			?>
			<div class="error notice">
				<p><?php _e( 'There has been an error. !', 'conditional-marketing-mailer' ); ?></p>
			</div>
			<?php
		}
		public function clear_log_block_id_req($hash){

			$array = get_option( 'WCMM_block_ip', array() );

			foreach($array as $key=>$val) {
				if ($val['id'] == $hash){
					unset($array[$key]);
					update_option('WCMM_block_ip',$array);
					$this->update_notice();
					return;
				}
			}
			$this->error_notice();
			return;
		}
		public function blockip(){
			?>
			<h2><?php _e( 'Block IP', 'conditional-marketing-mailer' );?></h2>
			<?php
			if(isset($_GET["clear"])){
				$this->clear_log_block_id();
			}
			if(isset($_GET["clear_req"])){
				$this->clear_log_block_id_req(absint($_GET["clear_req"]));
			}
			?>

			<a  href="admin.php?page=blockip&clear=1" class="button button-secondary "><?php _e( 'Clear All Block IP\'s', 'conditional-marketing-mailer' );?></a>
			<?php
			$login_failed_option = get_option( 'WCMM_block_ip', array() );
			?>
				<table class="failed_login_rep">
			<?php
			if(!empty($login_failed_option)){
				?>
					<tr>

						<th><?php _e( 'IP', 'conditional-marketing-mailer' );?></th>
						<th><?php _e( 'Date', 'conditional-marketing-mailer' );?></th>
						<th><?php _e( 'Country', 'conditional-marketing-mailer' );?></th>
						<th><?php _e( 'remove', 'conditional-marketing-mailer' );?></th>
					</tr>
				<?php
				foreach($login_failed_option as $log){
					?>
					<tr>
						<td><?php esc_html_e($log['ip']);?></td>
						<td><?php esc_html_e($log['date']);?></td>
						<td><?php esc_html_e($log['country']['country']);?></td>
						<td>
							<a  href="admin.php?page=blockip&clear_req=<?php esc_attr_e($log['id']);?>"><?php _e( 'Clear Block IP', 'conditional-marketing-mailer' );?></a>
						</td>
					</tr>
					<?php
				}
			}else{
				?>
				<tr>
					<td><?php _e( 'No Data Found', 'conditional-marketing-mailer' );?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php

		}
		public function WCMM_log(){
			if(isset($_GET["clear"])){
				$this->clear_log();
			}
			?>
			<h2><?php _e( 'Login Failed Logs', 'conditional-marketing-mailer' );?></h2>
			<a  href="admin.php?page=WCMMLOG&clear=1" class="button button-secondary "><?php _e( 'Clear log', 'conditional-marketing-mailer' );?></a>
			<?php
			$login_failed_option = get_option( 'WCMM_login_failed', array() );
			$login_failed_option = array_reverse($login_failed_option);
			?>
				<table class="failed_login_rep">
			<?php
			if(!empty($login_failed_option)){
				?>
					<tr>
						<th><?php _e( 'Username', 'conditional-marketing-mailer' );?></th>
						<th><?php _e( ' IP', 'conditional-marketing-mailer' );?></th>
						<th><?php _e( ' Date', 'conditional-marketing-mailer' );?></th>
						<th><?php _e( ' Country/City', 'conditional-marketing-mailer' );?></th>
					</tr>
				<?php
				foreach($login_failed_option as $log){
					?>
					<tr>
						<td><?php esc_html_e($log['username']);?></td>
						<td><?php esc_html_e($log['ip']);?></td>
						<td><?php esc_html_e($log['date']);?></td>
						<td>
						<?php
						if(isset($log['country']['country'])){ esc_html_e($log['country']['country']);}
						if(isset($log['country']['city'])){ esc_html_e(' / '.$log['country']['city']);}

						?>
						</td>
					</tr>
					<?php
				}
			}else{
				?>
				<tr>
					<td><?php _e( 'No Data Found', 'conditional-marketing-mailer' );?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}

		public function WCMM_options_page_html() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_GET['settings-updated'] ) ) {
				add_settings_error( 'WCMM_messages', 'WCMM_message', __( 'Settings Saved', 'conditional-marketing-mailer' ), 'updated' );
			}

			settings_errors( 'WCMM_messages' );

			?>

			<h2><?php _e( 'wp limit failed login attempts option', 'conditional-marketing-mailer' );?></h2>
			<div id="">
				<div id="dashboard-widgets" class="metabox-holder">
					<div id="" class="">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="dashboard_quick_press" class="postbox ">
								<h2 class="hndle ui-sortable-handle">
									<span>
										<span class="hide-if-no-js"><?php esc_html_e( get_admin_page_title() ); ?></span>
										<span class="hide-if-js"><?php esc_html_e( get_admin_page_title() ); ?></span>
									</span>
								</h2>
								<div class="inside">

									<form action="options.php" method="post">


										<div class="input-text-wrap" id="title-wrap">
											<?php
												settings_fields( 'WCMM' );
												do_settings_sections( 'WCMM' );
											?>
										</div>
										<p class="submit">
											<?php
												submit_button( 'Save Settings' );
											 ?>
											<br class="clear">
										</p>

									</form>
								</div>
							</div>

						</div>
					</div>


				</div>
			</div>
		 <?php
		}
	}

	$admin_setting_login_plugin = new WCMM_admin_setting();
}
if (!function_exists('WCMM_hkdc_admin_styles')) {

    function WCMM_hkdc_admin_styles($page)
    {
        if (isset($_GET['page']) && ($_GET['page'] == 'WCMM' || $_GET['page'] == 'WCMMLOG' || $_GET['page'] == 'blockip')) {
            wp_enqueue_style('failed_admin-css', WCMM_PLUGIN_URL . '/assets/css/admin-css.css?re=123');
        }
    }

    add_action('admin_print_styles', 'WCMM_hkdc_admin_styles');
}