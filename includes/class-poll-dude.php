<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/liaisontw/poll-dude
 * @since      1.0.0
 *
 * @package    poll-dude
 * @subpackage poll-dude/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    poll-dude
 * @subpackage poll-dude/includes
 * @author     Liaison Chang
 */
class Poll_Dude {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Poll_Dude_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	//protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $name    The string used to uniquely identify this plugin.
	 */
	protected $name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	/**
	 * The utility of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $utility    The instance of Poll_Dude_Utility Class of the plugin.
	 */
	public $utility;
	/**
	 * The shortcode of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $shortcode    The instance of Poll_Dude_Shortcode Class of the plugin.
	 */
	public $shortcode;
	public $admin;
	public $public;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'POLL_DUDE_VERSION' ) ) {
			$this->version = POLL_DUDE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->name = 'poll-dude';
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-poll-dude-utility.php';
		

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-poll-dude-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-poll-dude-shortcodes.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-poll-dude-widget.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-poll-dude-public.php';
		

		//$this->loader = new Poll_Dude_Loader();
		$this->utility = new poll_dude\Poll_Dude_Utility();
		if(isset($this->utility)){
			$this->shortcode = new Poll_Dude_Shortcode($this->utility);
		}

	}

		/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->admin = new Poll_Dude_Admin( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->public = new Poll_Dude_Public( $this->get_plugin_name(), $this->get_version() );

	}


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->name;
	}

	public function get_admin() {
		return $this->admin;
	}

	public function get_shortcode() {
		return $this->shortcode;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
