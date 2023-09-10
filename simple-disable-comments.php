<?php

/**
 * Simple Disable Comments
 *
 * @package           simple_disable_comments
 * @author            Delower Hossain
 * @copyright         2023 Delower Hossain
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Disable Comments
 * Plugin URI:        https://www.delowerhossain.com
 * Description:       Simple Disable Comments is a powerful WordPress plugin designed to give website administrators full control over comments on their WordPress websites. With this plugin, you can easily manage and customize the commenting system to suit your website's needs, whether you want to completely disable comments globally or on specific post types.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Delower Hossain
 * Author URI:        https://www.delowerhossain.com
 * Text Domain:       simple-disable-comments
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 
//Avoiding Direct File Access

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



/**
 * Simple Disable Comments Options
 */

 class SimpleDisableComments {

	private $simple_disable_comments_options;



	private static $instance;

	public static function get_instance(){
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function __construct() {
		add_action( 'admin_menu', [ $this, 'simple_disable_comments_add_plugin_page' ] );
		add_action( 'admin_init', [ $this, 'simple_disable_comments_page_init' ] );
		add_action( 'init', [$this, 'init']);
		add_filter('comments_array', [$this, '__return_empty_array' ], 10, 2);

		add_filter('comments_open', [ $this, '__return_false' ], 20, 2);
		add_filter('pings_open', [ $this, '__return_false' ], 20, 2);	

		add_action( 'plugins_loaded', [$this, 'sdc_load_textdomain' ] );	
 
	}


	/**
	 * Load plugin textdomain.
	 */
	public function sdc_load_textdomain() {
		load_plugin_textdomain( 'simple-disable-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}

	public function init() {
	    if (is_admin_bar_showing()) {
	        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	    }		
	}

	public function simple_disable_comments_add_plugin_page() {
		add_options_page(
			'Simple Disable Comments', // page_title
			'Simple Disable Comments', // menu_title
			'manage_options', // capability
			'simple-disable-comments', // menu_slug
			array( $this, 'simple_disable_comments_create_admin_page' ) // function
		);

		remove_menu_page('edit-comments.php');
	}

	public function simple_disable_comments_create_admin_page() {
		$this->simple_disable_comments_options = get_option( 'simple_disable_comments_option_name' ); ?>

		<div class="wrap">
			<h2>Simple Disable Comments</h2>
			<p>The simplest way to disable comments from your website.</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'simple_disable_comments_option_group' );
					do_settings_sections( 'simple-disable-comments-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function simple_disable_comments_page_init() {
		register_setting(
			'simple_disable_comments_option_group', // option_group
			'simple_disable_comments_option_name', // option_name
			array( $this, 'simple_disable_comments_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'simple_disable_comments_setting_section', // id
			'Simple Disable Comments Settings', // title
			array( $this, 'simple_disable_comments_section_info' ), // callback
			'simple-disable-comments-admin' // page
		);

		add_settings_field(
			'remove_comments_links_from_admin_bar_0', // id
			'Remove comments links from admin bar', // title
			array( $this, 'remove_comments_links_from_admin_bar_0_callback' ), // callback
			'simple-disable-comments-admin', // page
			'simple_disable_comments_setting_section' // section
		);

		// add_settings_field(
		// 	'remove_comments_page_in_menu_1', // id
		// 	'Remove comments page in menu', // title
		// 	array( $this, 'remove_comments_page_in_menu_1_callback' ), // callback
		// 	'simple-disable-comments-admin', // page
		// 	'simple_disable_comments_setting_section' // section
		// );

		// add_settings_field(
		// 	'hide_existing_comments_2', // id
		// 	'Hide existing comments', // title
		// 	array( $this, 'hide_existing_comments_2_callback' ), // callback
		// 	'simple-disable-comments-admin', // page
		// 	'simple_disable_comments_setting_section' // section
		// );

		// add_settings_field(
		// 	'close_comments_on_the_front_end_3', // id
		// 	'Close comments on the front-end', // title
		// 	array( $this, 'close_comments_on_the_front_end_3_callback' ), // callback
		// 	'simple-disable-comments-admin', // page
		// 	'simple_disable_comments_setting_section' // section
		// );

		// add_settings_field(
		// 	'remove_comments_metabox_from_dashboard_4', // id
		// 	'Remove comments metabox from dashboard', // title
		// 	array( $this, 'remove_comments_metabox_from_dashboard_4_callback' ), // callback
		// 	'simple-disable-comments-admin', // page
		// 	'simple_disable_comments_setting_section' // section
		// );


		// Simple Disable Commnets Plugin Starts
	    global $pagenow;
	     
	    if ($pagenow === 'edit-comments.php') {
	        wp_safe_redirect(admin_url());
	        exit;
	    }
	 
	    // Remove comments metabox from dashboard
	    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	 
	    // Disable support for comments and trackbacks in post types
	    foreach (get_post_types() as $post_type) {
	        if (post_type_supports($post_type, 'comments')) {
	            remove_post_type_support($post_type, 'comments');
	            remove_post_type_support($post_type, 'trackbacks');
	        }
	    }

	}

	public function simple_disable_comments_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['remove_comments_links_from_admin_bar_0'] ) ) {
			$sanitary_values['remove_comments_links_from_admin_bar_0'] = $input['remove_comments_links_from_admin_bar_0'];
		}

		// if ( isset( $input['remove_comments_page_in_menu_1'] ) ) {
		// 	$sanitary_values['remove_comments_page_in_menu_1'] = $input['remove_comments_page_in_menu_1'];
		// }

		// if ( isset( $input['hide_existing_comments_2'] ) ) {
		// 	$sanitary_values['hide_existing_comments_2'] = $input['hide_existing_comments_2'];
		// }

		// if ( isset( $input['close_comments_on_the_front_end_3'] ) ) {
		// 	$sanitary_values['close_comments_on_the_front_end_3'] = $input['close_comments_on_the_front_end_3'];
		// }

		// if ( isset( $input['remove_comments_metabox_from_dashboard_4'] ) ) {
		// 	$sanitary_values['remove_comments_metabox_from_dashboard_4'] = $input['remove_comments_metabox_from_dashboard_4'];
		// }

		return $sanitary_values;
	}

	public function simple_disable_comments_section_info() {
		
	}

	public function remove_comments_links_from_admin_bar_0_callback() {
		printf(
			'<input type="checkbox" name="simple_disable_comments_option_name[remove_comments_links_from_admin_bar_0]" id="remove_comments_links_from_admin_bar_0" value="remove_comments_links_from_admin_bar_0" %s>',
			( isset( $this->simple_disable_comments_options['remove_comments_links_from_admin_bar_0'] ) && $this->simple_disable_comments_options['remove_comments_links_from_admin_bar_0'] === 'remove_comments_links_from_admin_bar_0' ) ? 'checked' : ''
		);
	}

	// public function remove_comments_page_in_menu_1_callback() {
	// 	printf(
	// 		'<input type="checkbox" name="simple_disable_comments_option_name[remove_comments_page_in_menu_1]" id="remove_comments_page_in_menu_1" value="remove_comments_page_in_menu_1" %s>',
	// 		( isset( $this->simple_disable_comments_options['remove_comments_page_in_menu_1'] ) && $this->simple_disable_comments_options['remove_comments_page_in_menu_1'] === 'remove_comments_page_in_menu_1' ) ? 'checked' : ''
	// 	);
	// }

	// public function hide_existing_comments_2_callback() {
	// 	printf(
	// 		'<input type="checkbox" name="simple_disable_comments_option_name[hide_existing_comments_2]" id="hide_existing_comments_2" value="hide_existing_comments_2" %s>',
	// 		( isset( $this->simple_disable_comments_options['hide_existing_comments_2'] ) && $this->simple_disable_comments_options['hide_existing_comments_2'] === 'hide_existing_comments_2' ) ? 'checked' : ''
	// 	);
	// }

	// public function close_comments_on_the_front_end_3_callback() {
	// 	printf(
	// 		'<input type="checkbox" name="simple_disable_comments_option_name[close_comments_on_the_front_end_3]" id="close_comments_on_the_front_end_3" value="close_comments_on_the_front_end_3" %s>',
	// 		( isset( $this->simple_disable_comments_options['close_comments_on_the_front_end_3'] ) && $this->simple_disable_comments_options['close_comments_on_the_front_end_3'] === 'close_comments_on_the_front_end_3' ) ? 'checked' : ''
	// 	);
	// }

	// public function remove_comments_metabox_from_dashboard_4_callback() {
	// 	printf(
	// 		'<input type="checkbox" name="simple_disable_comments_option_name[remove_comments_metabox_from_dashboard_4]" id="remove_comments_metabox_from_dashboard_4" value="remove_comments_metabox_from_dashboard_4" %s>',
	// 		( isset( $this->simple_disable_comments_options['remove_comments_metabox_from_dashboard_4'] ) && $this->simple_disable_comments_options['remove_comments_metabox_from_dashboard_4'] === 'remove_comments_metabox_from_dashboard_4' ) ? 'checked' : ''
	// 	);
	// }

}



if ( is_admin() ) {
	$simple_disable_comments = SimpleDisableComments::get_instance();
}
