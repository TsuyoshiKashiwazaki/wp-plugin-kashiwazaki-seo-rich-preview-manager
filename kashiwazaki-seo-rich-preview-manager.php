<?php
/**
 * Plugin Name: Kashiwazaki SEO Rich Preview Manager
 * Plugin URI: https://www.tsuyoshikashiwazaki.jp
 * Description: OGP・Twitter Card・PageMap・Meta Thumbnailを一元管理し、SERP・AIO・SNSでのリッチプレビュー表示を最適化するプラグイン。
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: 柏崎剛 (Tsuyoshi Kashiwazaki)
 * Author URI: https://www.tsuyoshikashiwazaki.jp/profile/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kashiwazaki-seo-rich-preview-manager
 * Domain Path: /languages
 *
 * @package KashiwazakiSeoRichPreviewManager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'KSRPM_VERSION' ) ) {
	define( 'KSRPM_VERSION', '2.0.0' );
}

// Plugin root file.
if ( ! defined( 'KSRPM_PLUGIN_FILE' ) ) {
	define( 'KSRPM_PLUGIN_FILE', __FILE__ );
}

// Plugin base name.
if ( ! defined( 'KSRPM_PLUGIN_BASENAME' ) ) {
	define( 'KSRPM_PLUGIN_BASENAME', plugin_basename( KSRPM_PLUGIN_FILE ) );
}

// Plugin directory path.
if ( ! defined( 'KSRPM_PLUGIN_PATH' ) ) {
	define( 'KSRPM_PLUGIN_PATH', plugin_dir_path( KSRPM_PLUGIN_FILE ) );
}

// Plugin directory URL.
if ( ! defined( 'KSRPM_PLUGIN_URL' ) ) {
	define( 'KSRPM_PLUGIN_URL', plugin_dir_url( KSRPM_PLUGIN_FILE ) );
}

// Includes directory path.
if ( ! defined( 'KSRPM_INCLUDES_PATH' ) ) {
	define( 'KSRPM_INCLUDES_PATH', KSRPM_PLUGIN_PATH . 'includes/' );
}

// Admin directory path.
if ( ! defined( 'KSRPM_ADMIN_PATH' ) ) {
	define( 'KSRPM_ADMIN_PATH', KSRPM_PLUGIN_PATH . 'admin/' );
}

// Public directory path.
if ( ! defined( 'KSRPM_PUBLIC_PATH' ) ) {
	define( 'KSRPM_PUBLIC_PATH', KSRPM_PLUGIN_PATH . 'public/' );
}

// Assets directory URL.
if ( ! defined( 'KSRPM_ASSETS_URL' ) ) {
	define( 'KSRPM_ASSETS_URL', KSRPM_PLUGIN_URL . 'assets/' );
}

// Option name.
if ( ! defined( 'KSRPM_OPTION_NAME' ) ) {
	define( 'KSRPM_OPTION_NAME', 'ksrpm_options' );
}

/**
 * Get default options.
 *
 * @return array Default options.
 */
function ksrpm_get_default_options() {
	return array(
		'enable_ogp'                => true,
		'enable_twitter_card'       => true,
		'enable_meta_thumbnail'     => true,
		'enable_pagemap'            => true,
		'enable_robots_max_image'   => false,
		'default_og_type'           => 'website',
		'default_twitter_card_type' => 'summary_large_image',
		'site_name'                 => get_bloginfo( 'name' ),
		'fb_app_id'                 => '',
		'twitter_site'              => '',
		'default_image'             => '',
		'enable_for_post_types'     => array( 'post', 'page' ),
	);
}

/**
 * Class autoloader.
 *
 * @param string $class_name Class name to autoload.
 */
function ksrpm_autoloader( $class_name ) {
	// Check if the class belongs to this plugin.
	if ( strpos( $class_name, 'KSRPM_' ) !== 0 ) {
		return;
	}

	// Convert class name to file path.
	$class_name = str_replace( 'KSRPM_', '', $class_name );
	$class_name = strtolower( $class_name );
	$class_name = str_replace( '_', '-', $class_name );

	// Possible file paths.
	$paths = array(
		KSRPM_INCLUDES_PATH . 'class-' . $class_name . '.php',
		KSRPM_ADMIN_PATH . 'class-' . $class_name . '.php',
		KSRPM_PUBLIC_PATH . 'class-' . $class_name . '.php',
	);

	// Try to load the file.
	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			return;
		}
	}
}
spl_autoload_register( 'ksrpm_autoloader' );

/**
 * Main plugin class.
 */
class Kashiwazaki_Seo_Rich_Preview_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var Kashiwazaki_Seo_Rich_Preview_Manager|null
	 */
	private static $instance = null;

	/**
	 * Options cache.
	 *
	 * @var array|null
	 */
	private $options = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Kashiwazaki_Seo_Rich_Preview_Manager Singleton instance.
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
		// Initialize admin and metabox classes early (for admin area only).
		if ( is_admin() ) {
			KSRPM_Admin::get_instance();
			KSRPM_Metabox::get_instance();
		}

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Core hooks.
		add_action( 'init', array( $this, 'init' ) );

		// Frontend hooks.
		add_action( 'wp_head', array( $this, 'output_ogp_tags' ), 1 );

		// Check for old plugin conflict on every admin load.
		add_action( 'admin_init', array( $this, 'maybe_deactivate_old_plugin' ) );

		// Admin notice for old plugin deactivation.
		add_action( 'admin_notices', array( $this, 'show_old_plugin_deactivated_notice' ) );

		// Activation/Deactivation hooks.
		register_activation_hook( KSRPM_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( KSRPM_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		// Load text domain.
		load_plugin_textdomain(
			'kashiwazaki-seo-rich-preview-manager',
			false,
			dirname( KSRPM_PLUGIN_BASENAME ) . '/languages'
		);

		// Load dependencies.
		$this->load_dependencies();
	}

	/**
	 * Load dependencies.
	 */
	private function load_dependencies() {
		// Classes will be autoloaded via ksrpm_autoloader.
		// No explicit require_once needed.
	}

	/**
	 * Show admin notice when old plugin was auto-deactivated.
	 */
	public function show_old_plugin_deactivated_notice() {
		if ( ! get_transient( 'ksrpm_old_plugin_deactivated' ) ) {
			return;
		}

		delete_transient( 'ksrpm_old_plugin_deactivated' );

		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			esc_html__(
				'Kashiwazaki SEO OGP Manager（旧プラグイン）が検出されたため、自動的に無効化しました。設定とデータはこのプラグイン（Kashiwazaki SEO Rich Preview Manager）に引き継がれています。旧プラグインは削除して問題ありません。',
				'kashiwazaki-seo-rich-preview-manager'
			)
		);
	}

	/**
	 * Initialize Kashiwazaki SEO Rich Preview Manager and output OGP tags.
	 *
	 * This method always initializes the manager so that
	 * Twitter Card, Meta Thumbnail, and PageMap hooks are registered
	 * regardless of whether OGP itself is enabled.
	 */
	public function output_ogp_tags() {
		// Always initialize so all hooks (Twitter, Thumbnail, PageMap) are registered.
		$manager = KSRPM_Rich_Preview_Manager::get_instance();

		$options = $this->get_options();
		if ( ! empty( $options['enable_ogp'] ) ) {
			$manager->output_ogp_meta_tags();
		}
	}

	/**
	 * Get options.
	 *
	 * @return array Plugin options.
	 */
	public function get_options() {
		if ( null === $this->options ) {
			$this->options = wp_parse_args(
				get_option( KSRPM_OPTION_NAME, array() ),
				ksrpm_get_default_options()
			);
		}
		return $this->options;
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Check minimum requirements.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			deactivate_plugins( KSRPM_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'このプラグインはPHP 7.4以上が必要です。', 'kashiwazaki-seo-rich-preview-manager' ),
				esc_html__( 'プラグイン有効化エラー', 'kashiwazaki-seo-rich-preview-manager' ),
				array( 'back_link' => true )
			);
		}

		global $wp_version;
		if ( version_compare( $wp_version, '6.0', '<' ) ) {
			deactivate_plugins( KSRPM_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'このプラグインはWordPress 6.0以上が必要です。', 'kashiwazaki-seo-rich-preview-manager' ),
				esc_html__( 'プラグイン有効化エラー', 'kashiwazaki-seo-rich-preview-manager' ),
				array( 'back_link' => true )
			);
		}

		// Migrate from old plugin (kashiwazaki-seo-ogp-manager).
		$this->maybe_migrate_from_old_plugin();

		// Add default options if not exist.
		if ( ! get_option( KSRPM_OPTION_NAME ) ) {
			add_option( KSRPM_OPTION_NAME, ksrpm_get_default_options() );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate old plugin (kashiwazaki-seo-ogp-manager) if active.
	 *
	 * Prevents duplicate OGP tag output when both plugins are installed.
	 */
	public function maybe_deactivate_old_plugin() {
		$old_plugin = 'kashiwazaki-seo-ogp-manager/kashiwazaki-seo-ogp-manager.php';

		if ( is_plugin_active( $old_plugin ) ) {
			deactivate_plugins( $old_plugin );

			// Show admin notice.
			set_transient( 'ksrpm_old_plugin_deactivated', true, 60 );
		}
	}

	/**
	 * Migrate options and post meta from old plugin (kashiwazaki-seo-ogp-manager).
	 */
	private function maybe_migrate_from_old_plugin() {
		// Migrate options.
		$old_options = get_option( 'ksom_options' );
		if ( false !== $old_options && ! get_option( KSRPM_OPTION_NAME ) ) {
			update_option( KSRPM_OPTION_NAME, $old_options );
			delete_option( 'ksom_options' );
		}

		// Migrate post meta keys.
		global $wpdb;
		$old_meta_keys = array(
			'_ksom_og_title'          => '_ksrpm_og_title',
			'_ksom_og_description'    => '_ksrpm_og_description',
			'_ksom_og_image'          => '_ksrpm_og_image',
			'_ksom_og_type'           => '_ksrpm_og_type',
			'_ksom_twitter_card_type' => '_ksrpm_twitter_card_type',
		);

		foreach ( $old_meta_keys as $old_key => $new_key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
					$new_key,
					$old_key
				)
			);
		}

		// Cleanup old transients.
		delete_transient( 'ksom_cache' );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();

		// Cleanup transients if any.
		delete_transient( 'ksrpm_cache' );
	}

}

/**
 * Initialize the plugin.
 *
 * @return Kashiwazaki_Seo_Rich_Preview_Manager Plugin instance.
 */
function ksrpm_init() {
	return Kashiwazaki_Seo_Rich_Preview_Manager::get_instance();
}
add_action( 'plugins_loaded', 'ksrpm_init' );
