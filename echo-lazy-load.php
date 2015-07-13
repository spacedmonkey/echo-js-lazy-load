<?php
/**
 * Echo Lazy Load
 *
 *
 * @package   Echo_Lazy_Load
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2015 Spacedmonkey
 *
 * @wordpress-plugin
 * Plugin Name:        Echo Lazy Load
 * Plugin URI:         https://www.github.com/spacedmonkey/echo-lazy-load
 * Description:        Echo Lazy Load
 * Version:            1.0.0
 * Author:             Jonathan Harris
 * Author URI:         http://www.jonathandavidharris.co.uk/
 * Text Domain:        echo-lazy-load
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:        /languages
 * GitHub Plugin URI:  https://www.github.com/spacedmonkey/echo-lazy-load
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Echo_Lazy_Load
 */
class Echo_Lazy_Load {

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

	protected $plugin_name = 'echo_lazy_load';

	/**
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
		'term_description'
	);

	/**
	 *
	 * @since     1.0.0
	 *
	 * @var string
	 */
	protected $lazy_load_settings = array( 'offset' => 100, 'throttle' => 250 );


	/**
	 *
	 * @since     1.0.0
	 *
	 * @var string
	 */
	protected $lazy_load_image_ajax = '';


	/**
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

		$this->lazy_load_image_placeholder = plugins_url( 'img/blank.gif', __FILE__ );
		$this->lazy_load_image_ajax        = plugins_url( 'img/ajax.gif', __FILE__ );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 5 );
		add_action( 'wp_head', array( $this, 'wp_head' ), 5 );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 99 );

		foreach ( $this->getFilters() as $filter ) {
			add_filter( $filter, array( $this, 'filter_content' ) );
		}

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
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_content( $content ) {

		if ( ! $this->isLazyLoadEnabled() ) {
			return $content;
		}

		// Don't lazy-load if the content has already been run through previously
		if ( false !== strpos( $content, 'data-echo' ) ) {
			return $content;
		}

		$placeholder_image = $this->getLazyLoadImagePlaceholder();

		// This is a pretty simple regex, but it works
		$content = preg_replace( '#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', sprintf( '<img${1}src="%s" data-echo="${2}"${3}><noscript><img${1}src="${2}"${3}></noscript>', $placeholder_image ), $content );

		return $content;
	}

	public function wp_head() {
		$image_url = $this->getLazyLoadImageAjax();
		echo "<style type='text/css' media='screen'>img[data-echo]{ background: #fff url('" . $image_url . "') no-repeat center center; } </style>";
	}

	/**
	 *
	 */
	public function wp_enqueue_scripts() {
		$script = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'echo.js' : 'echo.min.js';
		wp_enqueue_script( $this->getPluginName(), plugins_url( 'js/' . $script, __FILE__ ), array(), $this::VERSION, true );
		wp_localize_script( $this->getPluginName(), $this->getPluginName(), $this->getLazyLoadSettings() );
	}

	/**
	 * @param $src
	 * @param null $handle
	 *
	 * @return string
	 */
	function wp_footer() {

		echo '<script>echo.init(' . $this->getPluginName() . ');</script>' . "\n";
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return apply_filters( 'echo_lazy_load_filters', $this->filters );
	}

	/**
	 * @return array
	 */
	public function getLazyLoadSettings() {
		return apply_filters( 'echo_lazy_load_settings', $this->lazy_load_settings );
	}

	/**
	 * @return string
	 */
	public function getPluginName() {
		return $this->plugin_name;
	}

	/**
	 * @return boolean
	 */
	public function isLazyLoadEnabled() {

		if ( is_admin() ) {
			$this->lazy_load_enabled = false;
		}

		if ( is_feed() ) {
			$this->lazy_load_enabled = false;
		}

		if ( is_preview() ) {
			$this->lazy_load_enabled = false;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->lazy_load_enabled = false;
		}

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$this->lazy_load_enabled = false;
		}

		return apply_filters( 'echo_lazy_load_enabled', $this->lazy_load_enabled );
	}

	/**
	 * @return string
	 */
	public function getLazyLoadImageAjax() {
		return apply_filters( 'echo_lazy_load_placeholder', $this->lazy_load_image_ajax );
	}


	/**
	 * @return string
	 */
	public function getLazyLoadImagePlaceholder() {
		return apply_filters( 'echo_lazy_load_placeholder', $this->lazy_load_image_placeholder );
	}


}
add_action( 'plugins_loaded', array( 'Echo_Lazy_Load', 'get_instance' ) );
