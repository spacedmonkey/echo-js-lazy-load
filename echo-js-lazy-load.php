<?php
/**
 * Echo.js based lazy load plugin for WordPress
 *
 *
 * @package   Echo_Js_Lazy_Load
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2015 Spacedmonkey
 *
 * @wordpress-plugin
 * Plugin Name:        Echo.js Lazy Load
 * Plugin URI:         https://www.github.com/spacedmonkey/echo-js-lazy-load
 * Description:        Echo.js based lazy load plugin for WordPress
 * Version:            1.0.0
 * Author:             Jonathan Harris
 * Author URI:         http://www.jonathandavidharris.co.uk/
 * Text Domain:        echo-js-lazy-load
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:        /languages
 * GitHub Plugin URI:  https://www.github.com/spacedmonkey/echo-js-lazy-load
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Echo_Js_Lazy_Load
 */
class Echo_Js_Lazy_Load {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin name by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */

	protected $plugin_name = 'echo_js_lazy_load';

	/**
	 * List of filters to run this string replace on
	 *
	 * @since     1.0.0
	 *
	 * @var array
	 */
	protected $filters = array(
		'the_content',
		'get_the_excerpt',
		'widget_text',
		'post_thumbnail_html',
		'get_avatar',
		'get_comment_excerpt',
		'get_comment_text',
		'term_description',
	);

	/**
	 * Javascript settings array
	 *
	 * @since     1.0.0
	 *
	 * @var string
	 */
	protected $lazy_load_settings = array(
		'offset'   => 500,
		'throttle' => 250,
		'debounce' => 'true',
		'unload' => 'false',
	);


	/**
	 *
	 * URL of ajax image
	 *
	 * @since     1.0.0
	 *
	 * @var string
	 */
	protected $lazy_load_image_ajax = '';


	/**
	 *
	 * URL of placeholder image
	 *
	 * @since     1.0.0
	 *
	 * @var string
	 */
	protected $lazy_load_image_placeholder = '';

	/**
	 *
	 * @since     1.0.0
	 *
	 * @var boolean
	 */
	protected $lazy_load_enabled = true;


	/**
	 *
	 */
	private function __construct() {

		// Lets not both if not enabled
		if ( ! $this->is_lazy_load_enabled() ) {
			return;
		}

		$this->lazy_load_image_placeholder = plugins_url( 'img/blank.gif', __FILE__ );
		$this->lazy_load_image_ajax        = plugins_url( 'img/ajax.gif', __FILE__ );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 5 );
		add_action( 'wp_head', array( $this, 'wp_head' ), 5 );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 99 );
		add_action( 'init', array( $this, 'init' ) );
	}


	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * String replace on content to add data attribute
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_content( $content ) {

		// Lets not both if not enabled
		if ( ! $this->is_lazy_load_locally_enabled() ) {
			return $content;
		}

		$content = preg_replace_callback(
			'/<img[^>]+>/i',
			array( $this, 'change_img_markup' ),
			$content
		);

		return $content;
	}

	/**
	 *
	 * @param  $matches List of images
	 * @return $image  Image Tag
	 */
	function change_img_markup( $matches ) {

		$image = array_shift( $matches );

		$placeholder_image = $this->get_lazy_load_image_placeholder();

		$replace = array(
			'data-echo'               => array( 'src="' => sprintf( 'src="%s" data-echo="', $placeholder_image ) ),
			'data-echo-srcset'        => array( ' srcset' => ' data-echo-srcset' ),
			'class="'                 => array( '<img ' => '<img class="" ' ),
			'echo-image echo-loading' => array( 'class="' => 'class="echo-image echo-loading ' )
		);

		foreach ( $replace as $search_item => $terms ) {
			foreach ( $terms as $before => $after ) {
				if ( false === strpos( $image, $search_item ) ) {
					$image = str_replace( $before, $after, $image );
				}
			}
		}

		return $image;
	}

	/**
	 * Run filters on init.
	 *
	 */
	public function init() {

		// Filter filter content to list of filters
		foreach ( $this->get_filters() as $filter ) {
			add_filter( $filter, array( $this, 'filter_content' ) );
		}
	}

	/**
	 * Put CSS in header.
	 * To disable, return false on echo_js_lazy_load_ajax_image filter
	 */
	public function wp_head() {
		$image_url = $this->get_lazy_load_image_ajax();
		if ( $image_url ) {
			echo "<style type='text/css' media='screen'>.echo-loading{ background: #fff url('" . $image_url . "') no-repeat center center; } </style>";

		}
	}

	/**
	 * Output scripts with settings.
	 *
	 */
	public function wp_enqueue_scripts() {
		$script = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'echo.js' : 'echo.min.js';
		wp_enqueue_script( $this->get_plugin_name(), plugins_url( 'js/' . $script, __FILE__ ), array(), $this::VERSION, true );
		wp_localize_script( $this->get_plugin_name(), $this->get_plugin_name(), $this->get_lazy_load_settings() );
	}

	/**
	 * Init the script in footer
	 */
	function wp_footer() {
		$script_name = $this->get_plugin_name();
		echo '<script type="text/javascript">
				' . $script_name . '.debounce = (' . $script_name . '.debounce === "true");
				' . $script_name . '.unload = (' . $script_name . '.unload === "true");
				' . $script_name . '.callback = function ( elem, op ) {
						   if( op === "load" ) {
						        if ( elem.getAttribute("data-echo-srcset") !== null ) {
						            elem.setAttribute("srcset", elem.getAttribute("data-echo-srcset"));
						            elem.removeAttribute("data-echo-srcset");
						        }
								elem.classList.remove("echo-loading");
								elem.classList.add("echo-loaded");
						   }
						}
				echo.init(' . $script_name . ');
			  </script>' . "\n";
	}

	/**
	 * Filter the list of filters applied the lazy load string replace.
	 *
	 * @return array
	 */
	public function get_filters() {
		return (array) apply_filters( 'echo_js_lazy_load_filters', $this->filters );
	}

	/**
	 * Filter the settings.
	 * Should always return array
	 *
	 * @return array
	 */
	public function get_lazy_load_settings() {
		return (array) apply_filters( 'echo_js_lazy_load_settings', $this->lazy_load_settings );
	}

	/**
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Disable the string replace on the following conditions
	 * is admin, is ajax, is preview, is cron.
	 * This is filterable.
	 *
	 * @return boolean
	 */
	public function is_lazy_load_enabled() {

		$context           = '';
		$lazy_load_enabled = true;

		// Is in admin terminal
		if ( is_admin() ) {
			$context           = 'admin';
			$lazy_load_enabled = false;
		}

		// Is doing ajax
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$context           = 'ajax';
			$lazy_load_enabled = false;
		}

		// Is doing cron
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$context           = 'cron';
			$lazy_load_enabled = false;
		}

		/**
		 * Is echo js enabled, by context
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $enabled Is and isn't enabled.
		 * @param string $context Current context of where the filter is called
		 * @param string $filter Current filter
		 */

		return apply_filters( 'echo_js_lazy_load_enabled', $lazy_load_enabled, $context );
	}


	/**
	 * Disable the string replace on the following conditions
	 * is admin, is ajax, is preview, is cron.
	 * This is filterable.
	 *
	 * @return boolean
	 */
	public function is_lazy_load_locally_enabled() {
		global $wp_current_filter;

		$context                 = '';
		$filter                  = current_filter();
		$this->lazy_load_enabled = true;

		// Is in feed
		if ( is_feed() ) {
			$context           = 'feed';
			$this->lazy_load_enabled = false;
		}

		// Is in post preview
		if ( is_preview() ) {
			$context                 = 'preview';
			$this->lazy_load_enabled = false;
		}

		// Is in admin bar / avatar. This is a work around.
		if ( array( 'wp_footer', 'admin_bar_menu', 'get_avatar' ) == $wp_current_filter ) {
			$context                 = 'admin-bar';
			$this->lazy_load_enabled = false;
		}

		// Is in customizer
		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
			$context                 = 'customize_preview';
			$this->lazy_load_enabled = false;
		}

		/**
		 * Is echo js enabled, by context
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $enabled  Is and isn't enabled.
		 * @param string $context   Current context of where the filter is called
		 * @param string $filter    Current filter
		 */
		return apply_filters( 'echo_lazy_load_locally_enabled', $this->lazy_load_enabled, $context, $filter );
	}

	/**
	 * String of URL of loading image or falase if disabled
	 *
	 * @return string|boolean
	 */
	public function get_lazy_load_image_ajax() {
		return apply_filters( 'echo_js_lazy_load_ajax_image', $this->lazy_load_image_ajax );
	}


	/**
	 * Get placeholder image. This is filterable depending on context.
	 * You could for example have different avatar placeholder
	 *
	 * @return string url of placeholder image
	 */
	public function get_lazy_load_image_placeholder() {
		$context = current_filter();

		return apply_filters( 'echo_js_lazy_load_placeholder', $this->lazy_load_image_placeholder, $context );
	}
}

add_action( 'plugins_loaded', array( 'Echo_Js_Lazy_Load', 'get_instance' ) );
