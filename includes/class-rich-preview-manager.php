<?php
/**
 * Kashiwazaki SEO Rich Preview Manager Core Class
 *
 * Core management class for Kashiwazaki SEO Rich Preview Manager.
 * Handles initialization and hook registration for Twitter Card, Meta Thumbnail, PageMap, and Robots.
 *
 * @package KashiwazakiSeoRichPreviewManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSRPM_Rich_Preview_Manager class.
 *
 * Core management class that handles rich preview initialization and hook registration.
 *
 * @since 1.0.0
 */
class KSRPM_Rich_Preview_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var KSRPM_Rich_Preview_Manager|null
	 */
	private static $instance = null;

	/**
	 * Meta tags handler instance.
	 *
	 * @var KSRPM_Meta_Tags|null
	 */
	private $meta_tags = null;

	/**
	 * Image handler instance.
	 *
	 * @var KSRPM_Image_Handler|null
	 */
	private $image_handler = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Get singleton instance.
	 *
	 * @return KSRPM_Rich_Preview_Manager Singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_options();
		$this->init_handlers();
		$this->init_hooks();
	}

	/**
	 * Load plugin options.
	 */
	private function load_options() {
		$this->options = wp_parse_args(
			get_option( KSRPM_OPTION_NAME, array() ),
			ksrpm_get_default_options()
		);
	}

	/**
	 * Initialize handler instances.
	 */
	private function init_handlers() {
		$this->image_handler = new KSRPM_Image_Handler( $this->options );
		$this->meta_tags     = new KSRPM_Meta_Tags( $this->options, $this->image_handler );
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Frontend hooks.
		if ( ! is_admin() ) {
			// OGP output is handled by the main plugin class (Kashiwazaki_Seo_Rich_Preview_Manager::output_ogp_tags).
			add_action( 'wp_head', array( $this, 'output_twitter_card_tags' ), 2 );
			add_action( 'wp_head', array( $this, 'output_meta_thumbnail' ), 3 );
			add_action( 'wp_head', array( $this, 'output_pagemap' ), 4 );
			add_action( 'wp_head', array( $this, 'output_robots_max_image' ), 5 );
		}

		// Allow other plugins to hook into Kashiwazaki SEO Rich Preview Manager.
		do_action( 'ksrpm_manager_init', $this );
	}

	/**
	 * Output OGP meta tags.
	 *
	 * @since 1.0.0
	 */
	public function output_ogp_meta_tags() {
		// Check if OGP is enabled.
		if ( ! $this->is_ogp_enabled() ) {
			return;
		}

		// Allow plugins to disable OGP output for specific pages.
		if ( ! apply_filters( 'ksrpm_enable_ogp_output', true ) ) {
			return;
		}

		$this->meta_tags->output_ogp_tags();
	}

	/**
	 * Output Twitter Card tags.
	 *
	 * @since 1.0.0
	 */
	public function output_twitter_card_tags() {
		// Check if Twitter Cards is enabled.
		if ( ! $this->is_twitter_card_enabled() ) {
			return;
		}

		// Allow plugins to disable Twitter Card output for specific pages.
		if ( ! apply_filters( 'ksrpm_enable_twitter_card_output', true ) ) {
			return;
		}

		$this->meta_tags->output_twitter_card_tags();
	}

	/**
	 * Output meta thumbnail tag.
	 *
	 * @since 2.0.0
	 */
	public function output_meta_thumbnail() {
		if ( empty( $this->options['enable_meta_thumbnail'] ) ) {
			return;
		}

		if ( ! apply_filters( 'ksrpm_enable_meta_thumbnail_output', true ) ) {
			return;
		}

		$this->meta_tags->output_meta_thumbnail();
	}

	/**
	 * Output PageMap structured data.
	 *
	 * @since 2.0.0
	 */
	public function output_pagemap() {
		if ( empty( $this->options['enable_pagemap'] ) ) {
			return;
		}

		if ( ! apply_filters( 'ksrpm_enable_pagemap_output', true ) ) {
			return;
		}

		$this->meta_tags->output_pagemap();
	}

	/**
	 * Output robots max-image-preview meta tag.
	 *
	 * @since 2.0.0
	 */
	public function output_robots_max_image() {
		if ( empty( $this->options['enable_robots_max_image'] ) ) {
			return;
		}

		if ( ! apply_filters( 'ksrpm_enable_robots_max_image_output', true ) ) {
			return;
		}

		$this->meta_tags->output_robots_max_image();
	}

	/**
	 * Check if OGP is enabled.
	 *
	 * @return bool True if OGP is enabled, false otherwise.
	 */
	private function is_ogp_enabled() {
		return ! empty( $this->options['enable_ogp'] );
	}

	/**
	 * Check if Twitter Card is enabled.
	 *
	 * @return bool True if Twitter Card is enabled, false otherwise.
	 */
	private function is_twitter_card_enabled() {
		return ! empty( $this->options['enable_twitter_card'] );
	}

	/**
	 * Get plugin options.
	 *
	 * @return array Plugin options.
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get meta tags handler.
	 *
	 * @return KSRPM_Meta_Tags Meta tags handler instance.
	 */
	public function get_meta_tags_handler() {
		return $this->meta_tags;
	}

	/**
	 * Get image handler.
	 *
	 * @return KSRPM_Image_Handler Image handler instance.
	 */
	public function get_image_handler() {
		return $this->image_handler;
	}

	/**
	 * Refresh options cache.
	 *
	 * Useful when options are updated.
	 *
	 * @since 1.0.0
	 */
	public function refresh_options() {
		$this->load_options();

		// Reinitialize handlers with new options.
		$this->init_handlers();
	}
}
