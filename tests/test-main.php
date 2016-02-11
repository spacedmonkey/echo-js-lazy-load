<?php

class MainTest extends WP_UnitTestCase {

	protected $plugin_class = null;

	protected $content_with_image = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. <img src="http://www.example.com/image.jpg" alt="test" /> Duis faucibus quis diam in molestie. Donec elementum risus sodales tristique malesuada, nisl eros accumsan odio';

	protected $content_with_image_srcset = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. <img src="http://www.example.com/image.jpg" srcset="http://www.example.com/image.jpg 1w" alt="test" /> Duis faucibus quis diam in molestie. Donec elementum risus sodales tristique malesuada, nisl eros accumsan odio';

	protected $content_with_image_atr = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. <img src="http://www.example.com/image.jpg" data-echo="http://www.example.com/image.jpg" alt="test" /> Duis faucibus quis diam in molestie. Donec elementum risus sodales tristique malesuada, nisl eros accumsan odio';

	protected $content_with_image_no_src = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. <img class="wibble" alt="test" /> Duis faucibus quis diam in molestie. Donec elementum risus sodales tristique malesuada, nisl eros accumsan odio';

	protected $content_without_image = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis faucibus quis diam in molestie. Donec elementum, risus sodales tristique malesuada, nisl eros accumsan odio';

	function setUp() {
		parent::setUp();
		$this->plugin_class = Echo_Js_Lazy_Load::get_instance();
	}

	function tearDown() {
		parent::tearDown();
	}

	function testFilterContent() {

		add_filter( 'echo_js_lazy_load_enabled', '__return_true' );

		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_atr ) );
		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_srcset ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_no_src ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_without_image ) );

		remove_filter( 'echo_js_lazy_load_enabled', '__return_true' );
	}


	function testFilterContentPlaceholder() {

		add_filter( 'echo_js_lazy_load_enabled', '__return_true' );

		$placeholder_image = $this->plugin_class->get_lazy_load_image_placeholder();

		$this->assertContains( $placeholder_image, $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertContains( $placeholder_image, $this->plugin_class->filter_content( $this->content_with_image_srcset ) );
		$this->assertNotContains( $placeholder_image, $this->plugin_class->filter_content( $this->content_with_image_no_src ) );
		$this->assertNotContains( $placeholder_image, $this->plugin_class->filter_content( $this->content_with_image_atr ) );
		$this->assertNotContains( $placeholder_image, $this->plugin_class->filter_content( $this->content_without_image ) );

		remove_filter( 'echo_js_lazy_load_enabled', '__return_true' );
	}

	function testNoFiltering() {
		add_filter( 'echo_lazy_load_locally_enabled', '__return_false' );

		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_without_image ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_no_src ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_srcset ) );
		$this->assertNotContains( 'data-echo-srcset', $this->plugin_class->filter_content( $this->content_with_image_srcset ) );
		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_atr ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_without_image ), $this->content_without_image  );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image ), $this->content_with_image );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image_srcset ), $this->content_with_image_srcset );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image_atr ), $this->content_with_image_atr );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image_no_src ), $this->content_with_image_no_src );

		add_filter( 'echo_lazy_load_locally_enabled', '__return_false' );
	}


	function testSrcSet() {
		add_filter( 'echo_lazy_load_locally_enabled', '__return_true' );

		$this->assertNotContains( 'data-echo-srcset', $this->plugin_class->filter_content( $this->content_without_image ) );
		$this->assertNotContains( 'data-echo-srcset', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertNotContains( 'data-echo-srcset', $this->plugin_class->filter_content( $this->content_with_image_no_src ) );
		$this->assertNotContains( 'data-echo-srcset', $this->plugin_class->filter_content( $this->content_with_image_atr ) );

		$this->assertContains( 'data-echo-srcset', $this->plugin_class->filter_content( $this->content_with_image_srcset ) );

		$this->assertNotEquals( $this->plugin_class->filter_content( $this->content_with_image_srcset ), $this->content_with_image_srcset );

		add_filter( 'echo_lazy_load_locally_enabled', '__return_true' );
	}


	function test_arrays() {
		$this->assertTrue( is_array( $this->plugin_class->get_filters() ) );
		$this->assertTrue( is_array( $this->plugin_class->get_lazy_load_settings() ) );
	}

	function test_get_lazy_load_settings() {

		$elements = array(
			'offset',
			'throttle',
			'debounce',
			'unload',
		);

		foreach ( $elements as $element ) {
			$this->assertArrayHasKey( $element, $this->plugin_class->get_lazy_load_settings() );
		}

	}

	function test_get_filters() {
		$filters = array(
			'the_content',
			'get_the_excerpt',
			'widget_text',
			'post_thumbnail_html',
			'get_avatar',
			'get_comment_excerpt',
			'get_comment_text',
			'term_description',
		);
		$this->assertEquals( $filters, $this->plugin_class->get_filters() );
	}


}

