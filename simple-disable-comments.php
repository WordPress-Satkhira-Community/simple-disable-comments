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
		$this->simple_disable_comments_options = get_option( 'SDC_comments_setting' ); 
		error_log(print_r($this->simple_disable_comments_options, true));
		?>

		<div class="wrap">
			<h2>Simple Disable Comments</h2>
			<p>The simplest way to disable comments from your website.</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'SDC_comments' );
					do_settings_sections( 'simple-disable-comments-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function simple_disable_comments_page_init() {
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


		register_setting(
			'SDC_comments', 
			'SDC_comments_setting'
		);

		add_settings_section(
			'simple_disable_comments_setting_section',
			'Simple Disable Comments Settings',
			array( $this, 'simple_disable_comments_section_info' ), 
			'simple-disable-comments-admin'
		);

		add_settings_field(
			'rm_comments_from_admin_bar', 
			'Remove comments links from admin bar', 
			array( $this, 'remove_comments_links_from_admin_bar' ), 
			'simple-disable-comments-admin', 
			'simple_disable_comments_setting_section'
		);

		add_settings_field(
			'rm_comments_page_in_menu', // id
			'Remove comments page in menu', // title
			array( $this, 'rm_comments_page_in_menu_callback' ), // callback
			'simple-disable-comments-admin', // page
			'simple_disable_comments_setting_section' // section
		);

		add_settings_field(
			'hide_existing_comments', // id
			'Hide existing comments', // title
			array( $this, 'hide_existing_comments_callback' ), // callback
			'simple-disable-comments-admin', // page
			'simple_disable_comments_setting_section' // section
		);

		add_settings_field(
			'close_comments_frontend', // id
			'Close comments on the front-end', // title
			array( $this, 'close_comments_frontend_callback' ), // callback
			'simple-disable-comments-admin', // page
			'simple_disable_comments_setting_section' // section
		);

		add_settings_field(
			'rm_comments_meta_dashboard', // id
			'Remove comments metabox from dashboard', // title
			array( $this, 'rm_comments_meta_dashboard_callback' ), // callback
			'simple-disable-comments-admin', // page
			'simple_disable_comments_setting_section' // section
		);

	}


	public function simple_disable_comments_section_info() {
		
	}

	public function remove_comments_links_from_admin_bar() {
		$SDC_settings = get_option( 'SDC_comments_setting' );
		error_log(print_r($SDC_settings, true));

		printf(
			'<input type="checkbox" name="SDC_comments_setting[rm_comments_from_admin_bar]" id="rm_comments_from_admin_bar" value="1" %s>',
		 	isset( $SDC_settings['rm_comments_from_admin_bar'] ) ? checked( 1, $SDC_settings['rm_comments_from_admin_bar'], false) : ''
		);
	}

	public function rm_comments_page_in_menu_callback() {
		$SDC_settings = get_option( 'SDC_comments_setting' );
		printf(
			'<input type="checkbox" name="SDC_comments_setting[rm_comments_page]" id="rm_comments_page" value="1" %s>',
			isset( $SDC_settings['rm_comments_page'] ) ? checked( 1, $SDC_settings['rm_comments_page'], false) : ''
		);
	}

	public function hide_existing_comments_callback() {
		$SDC_settings = get_option( 'SDC_comments_setting' );

		printf(
			'<input type="checkbox" name="SDC_comments_setting[hide_existing_comments]" id="hide_existing_comments" value="1" %s>',
			isset( $SDC_settings['hide_existing_comments'] ) ? checked( 1, $SDC_settings['hide_existing_comments'], false) : ''
		);
	}

	public function close_comments_frontend_callback() {
		$SDC_settings = get_option( 'SDC_comments_setting' );
		
		printf(
			'<input type="checkbox" name="SDC_comments_setting[close_comments_frontend]" id="close_comments_frontend" value="1" %s>',
			isset( $SDC_settings['close_comments_frontend'] ) ? checked( 1, $SDC_settings['close_comments_frontend'], false) : ''
		);
	}

	public function rm_comments_meta_dashboard_callback() {
		$SDC_settings = get_option( 'SDC_comments_setting' );

		printf(
			'<input type="checkbox" name="SDC_comments_setting[rm_comments_meta_dashboard]" id="rm_comments_meta_dashboard" value="1" %s>',
			isset( $SDC_settings['rm_comments_meta_dashboard'] ) ? checked( 1, $SDC_settings['rm_comments_meta_dashboard'], false) : ''
		);
	}

}



if ( is_admin() ) {
	$simple_disable_comments = SimpleDisableComments::get_instance();
}
