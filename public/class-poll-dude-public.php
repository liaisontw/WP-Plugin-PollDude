<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 * @author     Your Name <email@example.com>
 */
class Poll_Dude_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('wp_enqueue_scripts', array($this, 'poll_dude_scripts') );
	}
	
	public function poll_dude_scripts() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
		//wp_enqueue_style('poll-dude', plugins_url('poll-dude/public/css/poll-dude-public.css'), false, POLL_DUDE_VERSION, 'all');
		//wp_enqueue_script('poll-dude', plugins_url('poll-dude/public/js/poll-dude-public.js'), array('jquery'), POLL_DUDE_VERSION, true);
		
		$poll_ajax_style = get_option('poll_ajax_style');
		wp_localize_script('poll-dude', 'pollsL10n', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'text_wait' => __('Your last request is still being processed. Please wait a while ...', 'wp-polls'),
			'text_valid' => __('Please choose a valid poll answer.', 'wp-polls'),
			'text_multiple' => __('Maximum number of choices allowed: ', 'wp-polls'),
			'show_loading' => (int) $poll_ajax_style['loading'],
			'show_fading' => (int) $poll_ajax_style['fading']
		));
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/poll-dude-public.css', array(), $this->version, 'all' );
		//wp_enqueue_style('poll-dude', plugins_url('poll-dude/public/css/poll-dude-public.css'), false, POLL_DUDE_VERSION, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_script('poll-dude', plugins_url('poll-dude/public/js/poll-dude-public.js'), array('jquery'), POLL_DUDE_VERSION, true);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/poll-dude-public.js', array( 'jquery' ), $this->version, true );
		/*
		$poll_ajax_style = get_option('poll_ajax_style');
		wp_localize_script('poll-dude', 'pollsL10n', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'text_wait' => __('Your last request is still being processed. Please wait a while ...', 'wp-polls'),
			'text_valid' => __('Please choose a valid poll answer.', 'wp-polls'),
			'text_multiple' => __('Maximum number of choices allowed: ', 'wp-polls'),
			'show_loading' => (int) $poll_ajax_style['loading'],
			'show_fading' => (int) $poll_ajax_style['fading']
		));
		*/
	}

}
