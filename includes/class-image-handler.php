<?php
/**
 * OGP Image Handler Class
 *
 * Handles automatic image selection logic for OGP.
 * Featured image priority with fallback processing.
 *
 * @package KashiwazakiSeoRichPreviewManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSRPM_Image_Handler class.
 *
 * Handles image selection with priority and fallback logic.
 *
 * @since 1.0.0
 */
class KSRPM_Image_Handler {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Minimum image width for OGP.
	 *
	 * @var int
	 */
	private $min_width = 200;

	/**
	 * Minimum image height for OGP.
	 *
	 * @var int
	 */
	private $min_height = 200;

	/**
	 * Recommended image width for OGP.
	 *
	 * @var int
	 */
	private $recommended_width = 1200;

	/**
	 * Recommended image height for OGP.
	 *
	 * @var int
	 */
	private $recommended_height = 630;

	/**
	 * Constructor.
	 *
	 * @param array $options Plugin options.
	 */
	public function __construct( $options ) {
		$this->options = $options;

		// Allow customization of image dimensions.
		$this->min_width          = apply_filters( 'ksrpm_min_image_width', $this->min_width );
		$this->min_height         = apply_filters( 'ksrpm_min_image_height', $this->min_height );
		$this->recommended_width  = apply_filters( 'ksrpm_recommended_image_width', $this->recommended_width );
		$this->recommended_height = apply_filters( 'ksrpm_recommended_image_height', $this->recommended_height );
	}

	/**
	 * Get image for a post.
	 *
	 * Priority order:
	 * 1. Custom OGP image (post meta)
	 * 2. Featured image (post thumbnail)
	 * 3. Default image from settings
	 *
	 * @param int $post_id Post ID.
	 * @return string|array Image URL or array of URLs.
	 */
	public function get_post_image( $post_id ) {
		if ( ! $post_id ) {
			return $this->get_default_image();
		}

		// Priority 1: Custom OGP image from post meta.
		$custom_image = get_post_meta( $post_id, '_ksrpm_og_image', true );
		if ( ! empty( $custom_image ) && $this->is_valid_image_url( $custom_image ) ) {
			return $this->encode_image_url( $custom_image );
		}

		// Priority 2: Featured image (post thumbnail).
		$featured_image = $this->get_featured_image( $post_id );
		if ( ! empty( $featured_image ) ) {
			return $featured_image;
		}

		// Priority 3: Default image.
		return $this->get_default_image();
	}

	/**
	 * Get featured image (post thumbnail).
	 *
	 * @param int $post_id Post ID.
	 * @return string Featured image URL or empty string.
	 */
	private function get_featured_image( $post_id ) {
		if ( ! has_post_thumbnail( $post_id ) ) {
			return '';
		}

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumbnail_id ) {
			return '';
		}

		// Get full size image URL.
		$image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );

		if ( empty( $image_url ) ) {
			return '';
		}

		// Validate image dimensions.
		if ( ! $this->validate_image_dimensions( $thumbnail_id ) ) {
			return '';
		}

		// Encode URL to handle Japanese and other non-ASCII characters.
		$image_url = $this->encode_image_url( $image_url );

		return apply_filters( 'ksrpm_featured_image_url', $image_url, $post_id );
	}

	/**
	 * Get default image from plugin settings.
	 *
	 * @return string Default image URL or empty string.
	 */
	public function get_default_image() {
		$default_image = '';

		if ( ! empty( $this->options['default_image'] ) ) {
			$default_image = $this->options['default_image'];
		}

		// Fallback to site logo if available.
		if ( empty( $default_image ) && has_custom_logo() ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			if ( $custom_logo_id ) {
				$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
				if ( $logo_url ) {
					$default_image = $logo_url;
				}
			}
		}

		// Fallback to site icon if available.
		if ( empty( $default_image ) && has_site_icon() ) {
			$default_image = get_site_icon_url( 512 );
		}

		// Encode URL to handle Japanese and other non-ASCII characters.
		if ( ! empty( $default_image ) ) {
			$default_image = $this->encode_image_url( $default_image );
		}

		return apply_filters( 'ksrpm_default_image_url', $default_image );
	}

	/**
	 * Validate image dimensions.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool True if valid, false otherwise.
	 */
	private function validate_image_dimensions( $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( empty( $metadata['width'] ) || empty( $metadata['height'] ) ) {
			return false;
		}

		$width  = (int) $metadata['width'];
		$height = (int) $metadata['height'];

		// Check minimum dimensions.
		if ( $width < $this->min_width || $height < $this->min_height ) {
			return false;
		}

		return apply_filters( 'ksrpm_validate_image_dimensions', true, $attachment_id, $width, $height );
	}

	/**
	 * Check if URL is a valid image URL.
	 *
	 * @param string $url URL to check.
	 * @return bool True if valid image URL, false otherwise.
	 */
	private function is_valid_image_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		// Check if URL is valid.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		// Check file extension using static method.
		if ( ! self::has_valid_image_extension( $url ) ) {
			return false;
		}

		return apply_filters( 'ksrpm_is_valid_image_url', true, $url );
	}

	/**
	 * Check if URL has a valid image extension.
	 *
	 * @param string $url URL to check.
	 * @return bool True if valid image extension, false otherwise.
	 */
	public static function has_valid_image_extension( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		// Get valid extensions from filter.
		$valid_extensions = apply_filters( 'ksrpm_valid_image_extensions', array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ) );

		// Parse URL and get extension.
		$parsed_url = wp_parse_url( $url );
		$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
		$extension  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		return in_array( $extension, $valid_extensions, true );
	}

	/**
	 * Get attachment ID from image URL.
	 *
	 * @param string $url Image URL.
	 * @return int|false Attachment ID or false if not found.
	 */
	private function get_attachment_id_from_url( $url ) {
		global $wpdb;

		// Remove protocol and host to search for relative path.
		$url = preg_replace( '/^https?:\/\/[^\/]+/i', '', $url );

		// Remove query string.
		$url = preg_replace( '/\?.*/', '', $url );

		// Search in database.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE guid LIKE %s AND post_type = 'attachment' LIMIT 1",
				'%' . $wpdb->esc_like( $url )
			)
		);
		// phpcs:enable

		if ( $attachment_id ) {
			return (int) $attachment_id;
		}

		// Try attachment_url_to_postid as fallback.
		$attachment_id = attachment_url_to_postid( $url );

		return $attachment_id ? (int) $attachment_id : false;
	}

	/**
	 * Get image metadata.
	 *
	 * Returns additional image metadata like width, height, alt text, etc.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array Image metadata.
	 */
	public function get_image_metadata( $attachment_id ) {
		$metadata = array(
			'url'    => '',
			'width'  => 0,
			'height' => 0,
			'alt'    => '',
		);

		if ( ! $attachment_id ) {
			return $metadata;
		}

		// Get URL.
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( $url ) {
			$metadata['url'] = $this->encode_image_url( $url );
		}

		// Get dimensions.
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! empty( $attachment_metadata['width'] ) ) {
			$metadata['width'] = (int) $attachment_metadata['width'];
		}
		if ( ! empty( $attachment_metadata['height'] ) ) {
			$metadata['height'] = (int) $attachment_metadata['height'];
		}

		// Get alt text.
		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $alt ) {
			$metadata['alt'] = sanitize_text_field( $alt );
		}

		return apply_filters( 'ksrpm_image_metadata', $metadata, $attachment_id );
	}

	/**
	 * Get recommended image dimensions.
	 *
	 * @return array Array with 'width' and 'height' keys.
	 */
	public function get_recommended_dimensions() {
		return array(
			'width'  => $this->recommended_width,
			'height' => $this->recommended_height,
		);
	}

	/**
	 * Get minimum image dimensions.
	 *
	 * @return array Array with 'width' and 'height' keys.
	 */
	public function get_minimum_dimensions() {
		return array(
			'width'  => $this->min_width,
			'height' => $this->min_height,
		);
	}

	/**
	 * Encode image URL to handle Japanese and other non-ASCII characters.
	 *
	 * This method properly encodes the filename portion of URLs while preserving
	 * the domain and path structure. This is necessary for Facebook OGP debugger
	 * and other tools that require properly encoded URLs.
	 *
	 * @param string $url Image URL to encode.
	 * @return string Properly encoded URL.
	 */
	private function encode_image_url( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		// Parse the URL into components.
		$parsed_url = wp_parse_url( $url );

		if ( ! $parsed_url || ! isset( $parsed_url['path'] ) ) {
			// If URL can't be parsed or has no path, return it as-is with basic escaping.
			return esc_url( $url );
		}

		// Split path into directory and filename.
		$path_parts = pathinfo( $parsed_url['path'] );
		$dirname    = isset( $path_parts['dirname'] ) ? $path_parts['dirname'] : '';
		$basename   = isset( $path_parts['basename'] ) ? $path_parts['basename'] : '';

		// Encode each segment of the directory path.
		$dir_segments = array_filter( explode( '/', $dirname ) );
		$encoded_dir  = '';
		foreach ( $dir_segments as $segment ) {
			$encoded_dir .= '/' . rawurlencode( rawurldecode( $segment ) );
		}

		// Encode the filename (basename).
		$encoded_basename = rawurlencode( rawurldecode( $basename ) );

		// Reconstruct the encoded path.
		$encoded_path = $encoded_dir . '/' . $encoded_basename;

		// Rebuild the URL.
		$encoded_url = '';
		if ( isset( $parsed_url['scheme'] ) ) {
			$encoded_url .= $parsed_url['scheme'] . '://';
		}
		if ( isset( $parsed_url['host'] ) ) {
			$encoded_url .= $parsed_url['host'];
		}
		if ( isset( $parsed_url['port'] ) ) {
			$encoded_url .= ':' . $parsed_url['port'];
		}
		$encoded_url .= $encoded_path;
		if ( isset( $parsed_url['query'] ) ) {
			$encoded_url .= '?' . $parsed_url['query'];
		}
		if ( isset( $parsed_url['fragment'] ) ) {
			$encoded_url .= '#' . $parsed_url['fragment'];
		}

		return apply_filters( 'ksrpm_encode_image_url', $encoded_url, $url );
	}
}
