<?php

class MainTest extends WP_UnitTestCase {

	protected $plugin_class = null;

	protected $plugin_state = null;

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
		$this->plugin_state                    = $this->plugin_class->isLazyLoadEnabled();
		$this->plugin_class->lazy_load_enabled = true;

		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_atr ) );

		$this->plugin_class->lazy_load_enabled = $this->plugin_state;
	}

	function testFilterContentNothing() {
		$this->plugin_state                    = $this->plugin_class->isLazyLoadEnabled();
		$this->plugin_class->lazy_load_enabled = true;

		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_without_image ) );

		$this->plugin_class->lazy_load_enabled = $this->plugin_state;
	}

	function testNoFiltering() {
		$this->plugin_state                    = $this->plugin_class->isLazyLoadEnabled();
		$this->plugin_class->lazy_load_enabled = false;

		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_without_image ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertNotContains( 'data-echo', $this->plugin_class->filter_content( $this->content_with_image_atr ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_without_image ), $this->plugin_class->filter_content( $this->content_without_image ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image ), $this->plugin_class->filter_content( $this->content_with_image ) );
		$this->assertEquals( $this->plugin_class->filter_content( $this->content_with_image_atr ), $this->plugin_class->filter_content( $this->content_with_image_atr ) );

		$this->plugin_class->lazy_load_enabled = $this->plugin_state;
	}


	function testgetLazyLoadSettings(){
		$this->assertArrayHasKey('offset', $this->plugin_class->getLazyLoadSettings());
		$this->assertArrayHasKey('throttle', $this->plugin_class->getLazyLoadSettings());
		$this->assertArrayHasKey('debounce', $this->plugin_class->getLazyLoadSettings());
		$this->assertArrayHasKey('unload', $this->plugin_class->getLazyLoadSettings());
	}

}

