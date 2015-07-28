<?php

class MainTest extends WP_UnitTestCase {

	protected $plugin_class = null;

	protected $content_with_image = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. <img src='http://www.example.com/image.jpg' alt='test' /> Duis faucibus quis diam in molestie. Donec elementum risus sodales tristique malesuada, nisl eros accumsan odio";

	protected $content_with_image_atr = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. <img src='blank.jpg' data-echo='http://www.example.com/image.jpg' alt='test' /> Duis faucibus quis diam in molestie. Donec elementum risus sodales tristique malesuada, nisl eros accumsan odio";

	protected $content_without_image = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis faucibus quis diam in molestie. Donec elementum, risus sodales tristique malesuada, nisl eros accumsan odio";

	function setUp() {
		parent::setUp();
		$this->plugin_class = Echo_Js_Lazy_Load::get_instance();
		$this->plugin_state = $this->plugin_class->isLazyLoadEnabled();
	}

	function tearDown() {
		parent::tearDown();
	}

	function testFilterContent() {

		add_filter( 'echo_js_lazy_load_enabled', '__return_true' );

		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_atr ) );

		remove_filter( 'echo_js_lazy_load_enabled', '__return_true' );
	}

	function testFilterContentNothing() {
		add_filter( 'echo_js_lazy_load_enabled', '__return_true' );

		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_without_image ) );

		remove_filter( 'echo_js_lazy_load_enabled', '__return_true' );
	}

	function testNoFiltering() {
		add_filter( 'echo_js_lazy_load_enabled', '__return_false' );

		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_without_image ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_atr ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_without_image ), $this->plugin_class->filter_content( $this->content_without_image ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image ), $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image_atr ), $this->plugin_class->filter_content( $this->content_with_image_atr ) );

		add_filter( 'echo_js_lazy_load_enabled', '__return_false' );
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

		foreach ( $filters as $filter ) {
			$this->assertArrayHasKey( $filter, $this->plugin_class->get_filters() );
		}
	}


}

