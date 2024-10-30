<?php

/*
  Plugin Name:Crypto ICO List Widget
  Description:Live cryptocurrency ICO list widget for WordPress provides status of active, upcoming and past ICO tokens depends on different platforms - Ethereum, Waves, Stellar, Komodo etc.
  Version:1.5
  Author:Cool Plugins
  Author URI:https://coolplugins.net/our-cool-plugins-list/
  License:GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain:cilw
 */

 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'CILW', '1.5' );
define( 'CILW_PRE_FILE', __FILE__ );
define( 'CILW_PATH', plugin_dir_path( CILW_PRE_FILE ) );
define('CILW_PLUGIN_DIR', plugin_dir_path( CILW_PRE_FILE ) );
define( 'CILW_URL', plugin_dir_url( CILW_PRE_FILE ) );


/**
 * Class Crypto_ICO_List_Widget
 */
final class Crypto_ICO_List_Widget {
	
	/**
	 * Plugin instance.
	 *
	 *
	 * @access private
	 */
	private static $instance = null;
	public $shortcode_obj=null;

	/**
	 * Get plugin instance.
	 *
	 *
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	
	
	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
	  	register_activation_hook( CILW_PRE_FILE, array( $this, 'cilw_activate' ) );
		register_deactivation_hook( CILW_PRE_FILE, array( $this, 'cilw_deactivate' ) );
		
	    $this->cilw_installation_date();
		add_action('init', array($this, 'cilw_save_update_icos'));
		add_action('init', array($this, 'cilw_cron_job_init'));
		
	  	add_action( 'cmb2_admin_init', array($this,'cilw_metaboxes'));
			if(is_admin()){

			add_action( 'admin_init',array($this,'cilw_check_installation_time'));
			add_action( 'admin_init',array($this,'cilw_spare_me'), 5 );
			add_action('upgrader_process_complete', array( $this, 'cilw_upgrade') );
			add_action( 'add_meta_boxes', array( $this,'register_cilw_meta_box') );
			add_filter( 'manage_cilw_posts_columns',array($this,'set_custom_edit_cilw_columns'));
			add_action( 'manage_cilw_posts_custom_column' ,array($this,'custom_cilw_column'), 10, 2 );

			add_action( 'save_post', array( $this,'save_cilw_settings'),10, 3 );
			add_action( 'admin_enqueue_scripts', array($this,'cilw_load_scripts'));
			
			}
			add_filter('cron_schedules', array($this, 'cilw_cron_schedules'));
			add_action( 'cilw_update_database', array($this, 'cilw_save_update_icos'));
		
	add_action( 'plugins_loaded', array( $this, 'cilw_init' ) );
	add_action( 'init',  array( $this,'cilw_post_type') );
	$this->cilw_includes();		
	}
	
	/*
	|--------------------------------------------------------------------------
	|  init function for running schedule
	|--------------------------------------------------------------------------
	*/
	public function cilw_cron_job_init(){
		if (!wp_next_scheduled('cilw_update_database')) {
			wp_schedule_event(time(), 'OnceInFiveHours', 'cilw_update_database');
		}
	}
	


	/**
	* Run when deactivate plugin.
	*/
	public  function cilw_deactivate() {
		GLOBAL $wpdb;
		wp_clear_scheduled_hook('cilw_update_database');
		
		$DB = new cilw_list_table();
		$DB->cilw_drop_table();
		
		delete_transient('cilw-database-ongoing-icos');
		delete_transient('cilw-database-upcoming-icos');
		delete_transient('cilw-database-past-icos');
				
	}

	/*
	|--------------------------------------------------------------------------
	|  cron custom schedules
	|--------------------------------------------------------------------------
	*/
	function cilw_cron_schedules($schedules)
	{

		if (!isset($schedules["OnceInFiveHours"])) {
			$schedules["OnceInFiveHours"] = array(
				'interval' => 60 * 60 * 5,
				'display' => __('Once every 5 hours')
			);
		}

		return $schedules;
	}

	public function cilw_upgrade(){
		GLOBAL $wpdb;
        $wp_postmeta=$wpdb->base_prefix.'postmeta';
        $wpdb->update($wp_postmeta,array('meta_key'=>'cilw_display_platform'),array('meta_key'=>'cilw_settings_cilw_display_platform'));
        $wpdb->update($wp_postmeta,array('meta_key'=>'cilw_start_date'),array('meta_key'=>'cilw_settings_cilw_start_date'));
        $wpdb->update($wp_postmeta,array('meta_key'=>'cilw_end_date'),array('meta_key'=>'cilw_settings_cilw_end_date'));
	}

	public  function cilw_activate() {

	}
		
		
	/*
	For ask for reviews code
	*/

	function cilw_installation_date(){
		$get_installation_time = strtotime("now");
		add_option('cilw_activation_time', $get_installation_time );
	}
		
	//check if review notice should be shown or not

	function cilw_check_installation_time() {

		$spare_me = get_option('cilw_crypto_spare_me');
	    if( !$spare_me ){
	        $install_date = get_option( 'cilw_activation_time' );
	        $past_date = strtotime( '-1 days' );
	      if ( $past_date >= $install_date ) {
	     	 add_action( 'admin_notices', array($this,'cilw_display_admin_notice'));
	     	}
		}
	}
		
	/**
	* Display Admin Notice, asking for a review
	**/
	function cilw_display_admin_notice() {
		// wordpress global variable
		global $pagenow;
		//    if( $pagenow == 'index.php' ){
		$dont_disturb = esc_url( get_admin_url() . '?spare_me=1' );
	    $plugin_info = get_plugin_data( __FILE__ , true, true );
	    $reviewurl = esc_url( 'https://wordpress.org/support/plugin/crypto-ico-list-widget/reviews/#new-post' );

		printf(__('<div class="cilw-review notice notice-info is-dismissible" style=" background: #fff !important;  border: 2px solid #d4d4d4 !important;   padding: 22px !important;  font-size: 16px !important;">You have been using <b> %s </b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !</br><div class="cilw-review-btn" style=
		  "margin-top: 10px !important;"><a href="%s" class="button button-primary" target=
	            "_blank">Rate Now!</a><a href="%s" class="cilw-review-done button button-secondary" style="margin-left: 10px !important;"> Already Done !</a></div></div>', $plugin_info['TextDomain']), $plugin_info['Name'], $reviewurl, $dont_disturb );

		// }
	}
		
		

	// remove the notice for the user if review already done or if the user does not want to
	function cilw_spare_me(){
	    if( isset( $_GET['spare_me'] ) && !empty( $_GET['spare_me'] ) ){
	        $spare_me = $_GET['spare_me'];
	        if( $spare_me == 1 ){
	            update_option( 'cilw_crypto_spare_me' , TRUE );
			}
		}
	}
		
		
	/**
	 * Save shortcode when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	function save_cilw_settings( $post_id, $post, $update ) {
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		        return;
		// AJAX? Not used here
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		        return;
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
		        return;
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
		        return;
		/*
		 * In production code, $slug should be set only once in the plugin,
		 * preferably as a class property, rather than in each function that needs it.
		 */
		$post_type = get_post_type($post_id);

		// If this isn't a 'book' post, don't update it.
		if ( "cilw" != $post_type ) return;
			// - Update the post's metadata.
			 update_option('cilw-post-id',$post_id);
	}
		
	// Creating custom options panels for crypto ico list widget
	function cilw_metaboxes(){
		require_once CILW_PLUGIN_DIR .'/includes/cilw-settings.php';
	}
		
		
	/**
	* Load plugin function files here.
	*/
	public function cilw_includes() {

		if ( file_exists(  CILW_PLUGIN_DIR . '/CMB2/init.php' ) ) {
				require_once CILW_PLUGIN_DIR . '/CMB2/init.php';
			}
		
		require_once __DIR__ . '/includes/cilw-db-helper-list.php';
		
		require_once __DIR__ . '/includes/cilw-shortcode.php';
		 	$this->shortcode_cmc=new cilw_Shortcode();
	}

	/**
	* Code you want to run when all other plugins loaded.
	*/
	public function cilw_init() {
		load_plugin_textdomain( 'cilw', false, basename(dirname(__FILE__)) . '/languages/');
	}
		
		
	/*
	*	CILW post type for settings panel
	*/
	function cilw_post_type() {

		$labels = array(
			'name'                  => _x( 'Crypto ICO List Widget', 'Post Type General Name', 'cilw' ),
			'singular_name'         => _x( 'Crypto ICO List Widget', 'Post Type Singular Name', 'cilw' ),
			'menu_name'             => __( 'Crypto ICO List', 'cilw' ),
			'name_admin_bar'        => __( 'Crypto ICO List', 'cilw' ),
			'archives'              => __( 'Item Archives', 'cilw' ),
			'attributes'            => __( 'Item Attributes', 'cilw' ),
			'parent_item_colon'     => __( 'Parent Item:', 'cilw' ),
			'all_items'             => __( 'All Shortcodes', 'cilw' ),
			'add_new_item'          => __( 'Add New Shortcode', 'cilw' ),
			'add_new'               => __( 'Add New', 'cilw' ),
			'new_item'              => __( 'New Item', 'cilw' ),
			'edit_item'             => __( 'Edit Item', 'cilw' ),
			'update_item'           => __( 'Update Item', 'cilw' ),
			'view_item'             => __( 'View Item', 'cilw' ),
			'view_items'            => __( 'View Items', 'cilw' ),
			'search_items'          => __( 'Search Item', 'cilw' ),
			'not_found'             => __( 'Not found', 'cilw' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'cilw' ),
			'featured_image'        => __( 'Featured Image', 'cilw' ),
			'set_featured_image'    => __( 'Set featured image', 'cilw' ),
			'remove_featured_image' => __( 'Remove featured image', 'cilw' ),
			'use_featured_image'    => __( 'Use as featured image', 'cilw' ),
			'insert_into_item'      => __( 'Insert into item', 'cilw' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'cilw' ),
			'items_list'            => __( 'Items list', 'cilw' ),
			'items_list_navigation' => __( 'Items list navigation', 'cilw' ),
			'filter_items_list'     => __( 'Filter items list', 'cilw' ),
		);
		$args = array(
			'label'                 => __( 'Crypto ICO List Widget', 'cilw' ),
			'description'           => __( 'Post Type Description', 'cilw' ),
			'labels'                => $labels,
			'supports'              => array( 'title' ),
			'taxonomies'            => array(''),
			'hierarchical'          => false,
			'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
			'show_ui'               => true,
			'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive' => false,  // it shouldn't have archive page
			'rewrite' => false,  // it shouldn't have rewrite rules
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'menu_icon'           => CILW_URL.'/assets/ico-icon.png',
			'capability_type'       => 'page',
		);
		register_post_type( 'cilw', $args );

	}
	
	//Registers the post type meta box
	public	function register_cilw_meta_box()
	{
		add_meta_box( 'cilw-shortcode', 'Crypto ICO List Widget shortcode',array($this,'cilw_shortcode_meta'), 'cilw', 'side', 'high' );
	}

	
	//Create a shortcode that returns post meta 
	public	function cilw_shortcode_meta()
	{
	    $id = get_the_ID();
	    $dynamic_attr='';
	   _e('<p>Paste this shortcode anywhere in (page/post)</p>');
	   
	   $dynamic_attr.="[crypto-ico-list-widget id=\"{$id}\"";
	   $dynamic_attr.=']';
	    ?>
	    <input type="text" class="cilw-regular-small" name="cilw_meta_box_text" id="cilw_meta_box_text" value="<?php echo htmlentities($dynamic_attr) ;?>" style="width:100%" readonly/>

	    <?php
	}
	
	//Return columns for the post table of 'cilw' post type.
	function set_custom_edit_cilw_columns($columns) {
	   $columns['shortcode'] = __( 'Shortcode', 'cilw' );
		return $columns;
	}

	//Filters the displayed columns in the terms list table.
	function custom_cilw_column( $column, $post_id ) {
		switch ( $column ) {
		case 'shortcode' :
		echo '<code>[crypto-ico-list-widget id="'.$post_id.'"]</code>';
		break;
		}
	}
	function cilw_save_update_icos(){

		cilw_get_api_data('ongoing');
		cilw_get_api_data('upcoming');
		cilw_get_api_data('past');

	}
	function cilw_load_scripts($hook) {
 		wp_enqueue_style( 'cilwp-custom-styles', CILW_URL.'assets/css/cilw-admin-styles.css');
 			}
	
}

	function Crypto_ICO_List_Widget() {
		return Crypto_ICO_List_Widget::get_instance();
	}



$GLOBALS['Crypto_ICO_List_Widget'] = Crypto_ICO_List_Widget();
 
 
