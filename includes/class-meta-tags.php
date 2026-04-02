<?php
/**
 * Meta Tags Class
 *
 * Handles OGP, Twitter Card, Meta Thumbnail, PageMap, and Robots meta tags output.
 *
 * @package KashiwazakiSeoRichPreviewManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSRPM_Meta_Tags class.
 *
 * Generates and outputs OGP and Twitter Card meta tags.
 *
 * @since 1.0.0
 */
class KSRPM_Meta_Tags {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Image handler instance.
	 *
	 * @var KSRPM_Image_Handler
	 */
	private $image_handler = null;

	/**
	 * Current post object.
	 *
	 * @var WP_Post|null
	 */
	private $post = null;

	/**
	 * Constructor.
	 *
	 * @param array                  $options       Plugin options.
	 * @param KSRPM_Image_Handler $image_handler Image handler instance.
	 */
	public function __construct( $options, $image_handler ) {
		$this->options       = $options;
		$this->image_handler = $image_handler;
		$this->post          = get_queried_object();
	}

	/**
	 * Output OGP meta tags.
	 *
	 * @since 1.0.0
	 */
	public function output_ogp_tags() {
		// Check if current post type is enabled for OGP.
		if ( ! $this->is_post_type_enabled() ) {
			return;
		}

		$tags = $this->get_ogp_tags();

		// Allow filtering of OGP tags.
		$tags = apply_filters( 'ksrpm_ogp_tags', $tags, $this->post );

		if ( empty( $tags ) ) {
			return;
		}

		echo "\n<!-- Kashiwazaki SEO Rich Preview Manager v" . esc_attr( KSRPM_VERSION ) . " -->\n";

		foreach ( $tags as $property => $content ) {
			if ( empty( $content ) ) {
				continue;
			}

			// Handle multiple values (like og:image).
			if ( is_array( $content ) ) {
				foreach ( $content as $value ) {
					$this->output_meta_tag( 'property', $property, $value );
				}
			} else {
				$this->output_meta_tag( 'property', $property, $content );
			}
		}

		echo "<!-- / Kashiwazaki SEO Rich Preview Manager -->\n\n";
	}

	/**
	 * Output Twitter Card meta tags.
	 *
	 * @since 1.0.0
	 */
	public function output_twitter_card_tags() {
		// Check if current post type is enabled for OGP.
		if ( ! $this->is_post_type_enabled() ) {
			return;
		}

		$tags = $this->get_twitter_card_tags();

		// Allow filtering of Twitter Card tags.
		$tags = apply_filters( 'ksrpm_twitter_card_tags', $tags, $this->post );

		if ( empty( $tags ) ) {
			return;
		}

		echo "<!-- Twitter Card -->\n";

		foreach ( $tags as $name => $content ) {
			if ( empty( $content ) ) {
				continue;
			}

			$this->output_meta_tag( 'name', $name, $content );
		}

		echo "<!-- / Twitter Card -->\n\n";
	}

	/**
	 * Get OGP meta tags.
	 *
	 * @return array OGP meta tags.
	 */
	private function get_ogp_tags() {
		$tags = array(
			'og:site_name'   => $this->get_site_name(),
			'og:locale'      => $this->get_locale(),
			'og:type'        => $this->get_og_type(),
			'og:title'       => $this->get_og_title(),
			'og:description' => $this->get_og_description(),
			'og:url'         => $this->get_og_url(),
			'og:image'       => $this->get_og_image(),
		);

		// Add image dimensions if available.
		$image_dimensions = $this->get_og_image_dimensions();
		if ( ! empty( $image_dimensions['width'] ) ) {
			$tags['og:image:width'] = $image_dimensions['width'];
		}
		if ( ! empty( $image_dimensions['height'] ) ) {
			$tags['og:image:height'] = $image_dimensions['height'];
		}

		// Add Facebook App ID if set.
		if ( ! empty( $this->options['fb_app_id'] ) ) {
			$tags['fb:app_id'] = $this->options['fb_app_id'];
		}

		// Add article-specific tags for posts with article type.
		$og_type = $this->get_og_type();
		if ( is_singular() && $this->post && 'article' === $og_type ) {
			$tags = array_merge( $tags, $this->get_article_tags() );
		}

		return $tags;
	}

	/**
	 * Get Twitter Card meta tags.
	 *
	 * @return array Twitter Card meta tags.
	 */
	private function get_twitter_card_tags() {
		$tags = array(
			'twitter:card'        => $this->get_twitter_card_type(),
			'twitter:title'       => $this->get_og_title(),
			'twitter:description' => $this->get_og_description(),
			'twitter:image'       => $this->get_twitter_image(),
		);

		// Add Twitter site username if set.
		if ( ! empty( $this->options['twitter_site'] ) ) {
			$twitter_site = $this->options['twitter_site'];

			// Add @ prefix if not present.
			if ( '@' !== substr( $twitter_site, 0, 1 ) ) {
				$twitter_site = '@' . $twitter_site;
			}

			$tags['twitter:site'] = $twitter_site;
		}

		return $tags;
	}

	/**
	 * Get article-specific OGP tags.
	 *
	 * @return array Article OGP tags.
	 */
	private function get_article_tags() {
		if ( ! $this->post instanceof WP_Post ) {
			return array();
		}

		$tags = array();

		// Published time.
		$tags['article:published_time'] = get_the_date( 'c', $this->post );

		// Modified time.
		$tags['article:modified_time'] = get_the_modified_date( 'c', $this->post );

		// Author.
		$author = get_the_author_meta( 'display_name', $this->post->post_author );
		if ( $author ) {
			$tags['article:author'] = $author;
		}

		// Categories.
		$categories = get_the_category( $this->post->ID );
		if ( ! empty( $categories ) ) {
			$tags['article:section'] = $categories[0]->name;
		}

		// Tags.
		$post_tags = get_the_tags( $this->post->ID );
		if ( ! empty( $post_tags ) && is_array( $post_tags ) ) {
			$tag_names = array();
			foreach ( $post_tags as $tag ) {
				$tag_names[] = $tag->name;
			}
			if ( ! empty( $tag_names ) ) {
				$tags['article:tag'] = $tag_names;
			}
		}

		return $tags;
	}

	/**
	 * Get site name.
	 *
	 * @return string Site name.
	 */
	private function get_site_name() {
		$site_name = ! empty( $this->options['site_name'] ) ? $this->options['site_name'] : get_bloginfo( 'name' );
		return apply_filters( 'ksrpm_site_name', $site_name );
	}

	/**
	 * Get locale.
	 *
	 * @return string Locale code.
	 */
	private function get_locale() {
		$locale = get_locale();

		// Convert WordPress locale to OGP format (e.g., ja_JP to ja_JP).
		$locale = str_replace( '-', '_', $locale );

		return apply_filters( 'ksrpm_locale', $locale );
	}

	/**
	 * Get OG type.
	 *
	 * @return string OG type.
	 */
	private function get_og_type() {
		$og_type = 'website';

		if ( is_singular() ) {
			// Check for custom post meta.
			$custom_type = get_post_meta( get_the_ID(), '_ksrpm_og_type', true );
			if ( ! empty( $custom_type ) ) {
				// Additional sanitization and validation for meta values.
				$custom_type   = sanitize_text_field( $custom_type );
				$allowed_types = array( 'article', 'website', 'blog', 'product', 'video' );
				$og_type       = in_array( $custom_type, $allowed_types, true ) ? $custom_type : 'website';
			} elseif ( is_singular( 'post' ) ) {
				$og_type = 'article';
			} else {
				$og_type = ! empty( $this->options['default_og_type'] ) ? $this->options['default_og_type'] : 'website';
			}
		}

		return apply_filters( 'ksrpm_og_type', $og_type, $this->post );
	}

	/**
	 * Get OG title.
	 *
	 * @return string OG title.
	 */
	private function get_og_title() {
		$title = '';

		// Check for custom post meta.
		if ( is_singular() && $this->post ) {
			$custom_title = get_post_meta( $this->post->ID, '_ksrpm_og_title', true );
			if ( ! empty( $custom_title ) ) {
				// Additional sanitization for meta values.
				$title = wp_strip_all_tags( sanitize_text_field( $custom_title ) );
			} else {
				$title = get_the_title( $this->post );
			}
		} elseif ( is_home() || is_front_page() ) {
			$title       = get_bloginfo( 'name' );
			$description = get_bloginfo( 'description' );
			if ( $description ) {
				$title .= ' | ' . $description;
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = get_the_author();
		} elseif ( is_archive() ) {
			$title = get_the_archive_title();
		} elseif ( is_search() ) {
			$title = sprintf( __( 'Search Results for: %s', 'kashiwazaki-seo-rich-preview-manager' ), get_search_query() );
		} else {
			$title = get_bloginfo( 'name' );
		}

		return apply_filters( 'ksrpm_og_title', $title, $this->post );
	}

	/**
	 * Get OG description.
	 *
	 * @return string OG description.
	 */
	private function get_og_description() {
		$description = '';

		// Check for custom post meta.
		if ( is_singular() && $this->post ) {
			$custom_description = get_post_meta( $this->post->ID, '_ksrpm_og_description', true );
			if ( ! empty( $custom_description ) ) {
				// Additional sanitization for meta values.
				$description = wp_strip_all_tags( sanitize_textarea_field( $custom_description ) );
			} elseif ( has_excerpt( $this->post ) ) {
				$description = get_the_excerpt( $this->post );
			} else {
				// Generate excerpt from content.
				$content     = wp_strip_all_tags( $this->post->post_content );
				$description = wp_trim_words( $content, 55, '...' );
			}
		} elseif ( is_home() || is_front_page() ) {
			$description = get_bloginfo( 'description' );
		} elseif ( is_category() ) {
			$description = category_description();
		} elseif ( is_tag() ) {
			$description = tag_description();
		} elseif ( is_author() ) {
			$description = get_the_author_meta( 'description' );
		} elseif ( is_archive() ) {
			$description = get_the_archive_description();
		}

		// Clean up description.
		$description = wp_strip_all_tags( $description );
		$description = str_replace( array( "\r", "\n" ), ' ', $description );
		$description = trim( $description );

		return apply_filters( 'ksrpm_og_description', $description, $this->post );
	}

	/**
	 * Get OG URL.
	 *
	 * @return string OG URL.
	 */
	private function get_og_url() {
		$url = '';

		if ( is_singular() && $this->post ) {
			$url = get_permalink( $this->post );
		} elseif ( is_home() || is_front_page() ) {
			$url = home_url( '/' );
		} elseif ( is_category() ) {
			$url = get_category_link( get_queried_object_id() );
		} elseif ( is_tag() ) {
			$url = get_tag_link( get_queried_object_id() );
		} elseif ( is_author() ) {
			$url = get_author_posts_url( get_queried_object_id() );
		} elseif ( is_archive() ) {
			$url = get_post_type_archive_link( get_post_type() );
		} else {
			// Use global $wp for safe URL construction.
			global $wp;
			$url = home_url( add_query_arg( array(), $wp->request ) );
		}

		return apply_filters( 'ksrpm_og_url', esc_url( $url ), $this->post );
	}

	/**
	 * Get OG image.
	 *
	 * @return string|array OG image URL(s).
	 */
	private function get_og_image() {
		$image_url = '';

		if ( is_singular() && $this->post ) {
			// Image handler applies priority: custom OGP image → featured image → default.
			$image_url = $this->image_handler->get_post_image( $this->post->ID );
		} else {
			// Use default image.
			$image_url = $this->image_handler->get_default_image();
		}

		return apply_filters( 'ksrpm_og_image', $image_url, $this->post );
	}

	/**
	 * Get OG image dimensions.
	 *
	 * @return array Array with 'width' and 'height' keys.
	 */
	private function get_og_image_dimensions() {
		$dimensions = array(
			'width'  => '',
			'height' => '',
		);

		if ( ! is_singular() || ! $this->post ) {
			return $dimensions;
		}

		// Try to get attachment ID from custom image.
		$custom_image = get_post_meta( $this->post->ID, '_ksrpm_og_image', true );
		$attachment_id = 0;

		if ( ! empty( $custom_image ) ) {
			// Try to get attachment ID from URL.
			$attachment_id = attachment_url_to_postid( $custom_image );
		}

		// If no custom image, try featured image.
		if ( ! $attachment_id && has_post_thumbnail( $this->post->ID ) ) {
			$attachment_id = get_post_thumbnail_id( $this->post->ID );
		}

		// Get dimensions from attachment.
		if ( $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
				$dimensions['width']  = (int) $metadata['width'];
				$dimensions['height'] = (int) $metadata['height'];
			}
		}

		return apply_filters( 'ksrpm_og_image_dimensions', $dimensions, $this->post );
	}

	/**
	 * Get Twitter image.
	 *
	 * @return string Twitter image URL.
	 */
	private function get_twitter_image() {
		// Use the same image as OG image.
		$image_url = $this->get_og_image();

		// If it's an array, get the first image.
		if ( is_array( $image_url ) ) {
			$image_url = ! empty( $image_url ) ? $image_url[0] : '';
		}

		return apply_filters( 'ksrpm_twitter_image', $image_url, $this->post );
	}

	/**
	 * Get Twitter Card type.
	 *
	 * @return string Twitter Card type.
	 */
	private function get_twitter_card_type() {
		$card_type = 'summary_large_image';

		// Check for custom post meta.
		if ( is_singular() && $this->post ) {
			$custom_card_type = get_post_meta( $this->post->ID, '_ksrpm_twitter_card_type', true );
			if ( ! empty( $custom_card_type ) ) {
				// Additional sanitization and validation for meta values.
				$custom_card_type   = sanitize_text_field( $custom_card_type );
				$allowed_card_types = array( 'summary', 'summary_large_image' );
				$card_type          = in_array( $custom_card_type, $allowed_card_types, true ) ? $custom_card_type : 'summary_large_image';
			} else {
				$card_type = ! empty( $this->options['default_twitter_card_type'] ) ? $this->options['default_twitter_card_type'] : 'summary_large_image';
			}
		}

		return apply_filters( 'ksrpm_twitter_card_type', $card_type, $this->post );
	}

	/**
	 * Check if current post type is enabled for OGP.
	 *
	 * @return bool True if post type is enabled, false otherwise.
	 */
	private function is_post_type_enabled() {
		// Allow OGP on non-singular pages (home, archive, etc.).
		if ( ! is_singular() ) {
			return true;
		}

		// Get current post type.
		$post_type = get_post_type();

		// Check if post type is enabled in options.
		$enabled_post_types = ! empty( $this->options['enable_for_post_types'] ) ? $this->options['enable_for_post_types'] : array();

		// Allow if post type is in the enabled list.
		return in_array( $post_type, $enabled_post_types, true );
	}

	/**
	 * Output meta thumbnail tag.
	 *
	 * Outputs <meta name="thumbnail"> for Google SERPs and AI Overviews.
	 *
	 * @since 2.0.0
	 */
	public function output_meta_thumbnail() {
		if ( ! $this->is_post_type_enabled() ) {
			return;
		}

		$image_url = $this->get_og_image();

		if ( is_array( $image_url ) ) {
			$image_url = ! empty( $image_url ) ? $image_url[0] : '';
		}

		if ( empty( $image_url ) ) {
			return;
		}

		$image_url = apply_filters( 'ksrpm_meta_thumbnail_url', $image_url, $this->post );

		echo "<!-- Meta Thumbnail -->\n";
		printf(
			'<meta name="thumbnail" content="%s" />' . "\n",
			esc_attr( $image_url )
		);
		echo "<!-- / Meta Thumbnail -->\n\n";
	}

	/**
	 * Output PageMap structured data.
	 *
	 * Outputs PageMap thumbnail DataObject in HTML comments
	 * for Google Programmable Search Engine and AI Overviews.
	 *
	 * @since 2.0.0
	 */
	public function output_pagemap() {
		if ( ! $this->is_post_type_enabled() ) {
			return;
		}

		$image_url = $this->get_og_image();

		if ( is_array( $image_url ) ) {
			$image_url = ! empty( $image_url ) ? $image_url[0] : '';
		}

		if ( empty( $image_url ) ) {
			return;
		}

		$dimensions = $this->get_og_image_dimensions();
		$width      = ! empty( $dimensions['width'] ) ? (int) $dimensions['width'] : 1200;
		$height     = ! empty( $dimensions['height'] ) ? (int) $dimensions['height'] : 630;

		$pagemap_data = apply_filters(
			'ksrpm_pagemap_data',
			array(
				'src'    => $image_url,
				'width'  => $width,
				'height' => $height,
			),
			$this->post
		);

		echo "<!--\n";
		echo "<PageMap>\n";
		echo "  <DataObject type=\"thumbnail\">\n";
		printf( "    <Attribute name=\"src\" value=\"%s\"/>\n", esc_attr( $pagemap_data['src'] ) );
		printf( "    <Attribute name=\"width\" value=\"%d\"/>\n", $pagemap_data['width'] );
		printf( "    <Attribute name=\"height\" value=\"%d\"/>\n", $pagemap_data['height'] );
		echo "  </DataObject>\n";
		echo "</PageMap>\n";
		echo "-->\n\n";
	}

	/**
	 * Output robots max-image-preview meta tag.
	 *
	 * @since 2.0.0
	 */
	public function output_robots_max_image() {
		echo '<!-- Robots max-image-preview -->' . "\n";
		echo '<meta name="robots" content="max-image-preview:large">' . "\n";
		echo '<!-- / Robots max-image-preview -->' . "\n\n";
	}

	/**
	 * Output a single meta tag.
	 *
	 * @param string $attribute Meta tag attribute (property or name).
	 * @param string $key       Meta tag key.
	 * @param string $content   Meta tag content.
	 */
	private function output_meta_tag( $attribute, $key, $content ) {
		// Sanitize and escape output.
		$attribute = esc_attr( $attribute );
		$key       = esc_attr( $key );
		$content   = esc_attr( $content );

		printf(
			'<meta %s="%s" content="%s" />' . "\n",
			$attribute,
			$key,
			$content
		);
	}
}
