<?php
/**
 * Admin Class
 *
 * Handles admin menu registration, settings page, and options management.
 *
 * @package KashiwazakiSeoRichPreviewManager
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KSRPM_Admin class.
 *
 * Manages admin interface for Kashiwazaki SEO Rich Preview Manager settings.
 *
 * @since 1.0.0
 */
class KSRPM_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var KSRPM_Admin|null
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Get singleton instance.
	 *
	 * @return KSRPM_Admin Singleton instance.
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
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'plugin_action_links_' . KSRPM_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		$this->page_hook = add_menu_page(
			__( 'Kashiwazaki SEO Rich Preview Manager', 'kashiwazaki-seo-rich-preview-manager' ),
			__( 'Kashiwazaki SEO Rich Preview Manager', 'kashiwazaki-seo-rich-preview-manager' ),
			'manage_options',
			'kashiwazaki-seo-rich-preview-manager',
			array( $this, 'render_settings_page' ),
			'dashicons-share',
			81
		);
	}

	/**
	 * Register settings (for sanitization only).
	 */
	public function register_settings() {
		register_setting(
			'ksrpm_settings_group',
			KSRPM_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Render settings page with card-based layout.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'このページにアクセスする権限がありません。', 'kashiwazaki-seo-rich-preview-manager' ) );
		}

		$this->load_options();
		$o = $this->options;
		$n = KSRPM_OPTION_NAME;
		?>
		<div class="wrap ksrpm-settings-wrap">
			<h1><?php esc_html_e( 'Kashiwazaki SEO Rich Preview Manager', 'kashiwazaki-seo-rich-preview-manager' ); ?></h1>
			<p class="ksrpm-page-description"><?php esc_html_e( 'OGP・Twitter Card・PageMap・Meta Thumbnailを一元管理し、SERP・AIO・SNSでのリッチプレビュー表示を最適化します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'ksrpm_settings_group' ); ?>

				<!-- ========== 出力機能 ON/OFF ========== -->
				<div class="ksrpm-card">
					<h2 class="ksrpm-card-title"><?php esc_html_e( '出力機能', 'kashiwazaki-seo-rich-preview-manager' ); ?></h2>
					<p class="ksrpm-card-description"><?php esc_html_e( 'HTMLの <head> に出力するタグを選択します。画像は「各投稿のOGP Settingsで指定した画像 → アイキャッチ画像 → 下記のデフォルト画像」の優先順で決まります。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>

					<table class="ksrpm-toggle-table">
						<tbody>
							<tr>
								<td class="ksrpm-toggle-cell">
									<label class="ksrpm-toggle">
										<input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enable_ogp]" value="1" <?php checked( ! empty( $o['enable_ogp'] ) ); ?> />
										<span class="ksrpm-toggle-slider"></span>
									</label>
								</td>
								<td>
									<strong>OGP (Open Graph Protocol)</strong>
									<span class="ksrpm-badge ksrpm-badge-sns">SNS</span>
									<p class="description"><?php esc_html_e( 'Facebook・LINEなどでシェアされた際のタイトル・画像・説明を制御する og:* メタタグを出力します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
									<p class="description ksrpm-note"><?php esc_html_e( '※ 他のSEOプラグイン（Yoast, RankMath等）が有効な場合、重複する可能性があります。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
								</td>
							</tr>
							<tr>
								<td class="ksrpm-toggle-cell">
									<label class="ksrpm-toggle">
										<input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enable_twitter_card]" value="1" <?php checked( ! empty( $o['enable_twitter_card'] ) ); ?> />
										<span class="ksrpm-toggle-slider"></span>
									</label>
								</td>
								<td>
									<strong>Twitter Card</strong>
									<span class="ksrpm-badge ksrpm-badge-sns">SNS</span>
									<p class="description"><?php esc_html_e( 'X (Twitter) でシェアされた際のカード表示を制御する twitter:* メタタグを出力します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
									<p class="description ksrpm-note"><?php esc_html_e( '※ 他のSEOプラグインが有効な場合、重複する可能性があります。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
								</td>
							</tr>
							<tr>
								<td class="ksrpm-toggle-cell">
									<label class="ksrpm-toggle">
										<input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enable_meta_thumbnail]" value="1" <?php checked( ! empty( $o['enable_meta_thumbnail'] ) ); ?> />
										<span class="ksrpm-toggle-slider"></span>
									</label>
								</td>
								<td>
									<strong>Meta Thumbnail</strong>
									<span class="ksrpm-badge ksrpm-badge-serp">SERP / AIO</span>
									<p class="description"><?php esc_html_e( 'Google検索結果やAI Overviewsのサムネイル候補となる <meta name="thumbnail"> タグを出力します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
									<p class="description ksrpm-note"><?php esc_html_e( '※ 一般的なSEOプラグインはこのタグを出力しないため、重複の可能性は低いです。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
								</td>
							</tr>
							<tr>
								<td class="ksrpm-toggle-cell">
									<label class="ksrpm-toggle">
										<input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enable_pagemap]" value="1" <?php checked( ! empty( $o['enable_pagemap'] ) ); ?> />
										<span class="ksrpm-toggle-slider"></span>
									</label>
								</td>
								<td>
									<strong>PageMap</strong>
									<span class="ksrpm-badge ksrpm-badge-serp">SERP / AIO</span>
									<p class="description"><?php esc_html_e( 'Google Programmable Search Engine等で使用されるPageMap thumbnail DataObjectをHTMLコメントで出力します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
									<p class="description ksrpm-note"><?php esc_html_e( '※ 一般的なSEOプラグインはこのデータを出力しないため、重複の可能性は低いです。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
								</td>
							</tr>
							<tr>
								<td class="ksrpm-toggle-cell">
									<label class="ksrpm-toggle">
										<input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enable_robots_max_image]" value="1" <?php checked( ! empty( $o['enable_robots_max_image'] ) ); ?> />
										<span class="ksrpm-toggle-slider"></span>
									</label>
								</td>
								<td>
									<strong>Robots max-image-preview</strong>
									<span class="ksrpm-badge ksrpm-badge-serp">SERP</span>
									<p class="description"><?php esc_html_e( 'Google検索結果で大きい画像プレビューを許可する <meta name="robots" content="max-image-preview:large"> を出力します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
									<p class="description ksrpm-note"><?php esc_html_e( '※ WordPress 5.7以降はWP本体が同じタグを自動出力するため、通常はOFFで問題ありません。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<div class="ksrpm-duplicate-check-area">
						<button type="button" class="button ksrpm-run-duplicate-check"><?php esc_html_e( '一括重複チェック', 'kashiwazaki-seo-rich-preview-manager' ); ?></button>
						<span class="ksrpm-check-spinner" style="display:none;"><?php esc_html_e( 'チェック中...', 'kashiwazaki-seo-rich-preview-manager' ); ?></span>
						<div class="ksrpm-duplicate-check-result" style="display:none;">
							<table class="ksrpm-check-result-table">
								<thead>
									<tr>
										<th><?php esc_html_e( '機能', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
										<th><?php esc_html_e( '状態', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
										<th><?php esc_html_e( '詳細', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
									</tr>
								</thead>
								<tbody id="ksrpm-check-results-body"></tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- ========== OGP詳細設定 ========== -->
				<div class="ksrpm-card">
					<h2 class="ksrpm-card-title"><?php esc_html_e( 'OGP詳細設定', 'kashiwazaki-seo-rich-preview-manager' ); ?></h2>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'サイト名', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( $n ); ?>[site_name]" value="<?php echo esc_attr( $o['site_name'] ?? '' ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( '空欄の場合はWordPressのサイトタイトルを使用します。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'デフォルト og:type', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
							<td>
								<?php
								$og_type  = $o['default_og_type'] ?? 'website';
								$og_types = array(
									'website' => __( 'ウェブサイト', 'kashiwazaki-seo-rich-preview-manager' ),
									'article' => __( '記事', 'kashiwazaki-seo-rich-preview-manager' ),
									'blog'    => __( 'ブログ', 'kashiwazaki-seo-rich-preview-manager' ),
								);
								?>
								<select name="<?php echo esc_attr( $n ); ?>[default_og_type]">
									<?php foreach ( $og_types as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $og_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( '個別設定がないページで使用。標準投稿(post)は常に「記事」扱いです。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'デフォルト Twitter Card', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
							<td>
								<?php
								$card_type  = $o['default_twitter_card_type'] ?? 'summary_large_image';
								$card_types = array(
									'summary'             => __( 'サマリー', 'kashiwazaki-seo-rich-preview-manager' ),
									'summary_large_image' => __( '大きい画像付きサマリー', 'kashiwazaki-seo-rich-preview-manager' ),
								);
								?>
								<select name="<?php echo esc_attr( $n ); ?>[default_twitter_card_type]">
									<?php foreach ( $card_types as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $card_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( '「大きい画像付きサマリー」がおすすめです。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<!-- ========== SNSアカウント ========== -->
				<div class="ksrpm-card">
					<h2 class="ksrpm-card-title"><?php esc_html_e( 'SNSアカウント', 'kashiwazaki-seo-rich-preview-manager' ); ?></h2>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'X (Twitter) ユーザー名', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( $n ); ?>[twitter_site]" value="<?php echo esc_attr( $o['twitter_site'] ?? '' ); ?>" class="regular-text" placeholder="username" />
								<p class="description"><?php esc_html_e( '@なしで入力してください（自動で付与されます）。Twitter Cardの <twitter:site> として出力されます。不要なら空欄のままで問題ありません。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Facebook App ID', 'kashiwazaki-seo-rich-preview-manager' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( $n ); ?>[fb_app_id]" value="<?php echo esc_attr( $o['fb_app_id'] ?? '' ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Facebookのシェア分析（Insights）に使用するアプリIDです。<fb:app_id> として出力されます。不要なら空欄のままで問題ありません。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<!-- ========== デフォルト画像 ========== -->
				<div class="ksrpm-card">
					<h2 class="ksrpm-card-title"><?php esc_html_e( 'デフォルト画像', 'kashiwazaki-seo-rich-preview-manager' ); ?></h2>
					<p class="ksrpm-card-description"><?php esc_html_e( 'OGP Settingsの個別画像もアイキャッチ画像もない場合に、すべての出力機能で使用されるフォールバック画像です。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>

					<div class="ksrpm-image-upload">
						<?php $default_image = $o['default_image'] ?? ''; ?>
						<input type="url" name="<?php echo esc_attr( $n ); ?>[default_image]" id="ksrpm_default_image" value="<?php echo esc_url( $default_image ); ?>" class="large-text" />
						<p>
							<button type="button" class="button ksrpm-upload-image-button"><?php esc_html_e( '画像を選択', 'kashiwazaki-seo-rich-preview-manager' ); ?></button>
							<button type="button" class="button ksrpm-remove-image-button"><?php esc_html_e( '削除', 'kashiwazaki-seo-rich-preview-manager' ); ?></button>
						</p>
						<?php if ( $default_image ) : ?>
							<div class="ksrpm-image-preview">
								<img src="<?php echo esc_url( $default_image ); ?>" alt="" />
							</div>
						<?php endif; ?>
						<p class="description"><?php esc_html_e( '推奨サイズ: 1200 x 630px', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>
					</div>
				</div>

				<!-- ========== 対象投稿タイプ ========== -->
				<div class="ksrpm-card">
					<h2 class="ksrpm-card-title"><?php esc_html_e( '対象投稿タイプ', 'kashiwazaki-seo-rich-preview-manager' ); ?></h2>
					<p class="ksrpm-card-description"><?php esc_html_e( '選択した投稿タイプでメタボックスの表示とすべてのリッチプレビュータグ出力が有効になります。', 'kashiwazaki-seo-rich-preview-manager' ); ?></p>

					<?php
					$selected_types = $o['enable_for_post_types'] ?? array( 'post', 'page' );
					$post_types     = get_post_types( array( 'public' => true ), 'objects' );
					?>
					<p>
						<button type="button" class="button button-small ksrpm-select-all-post-types"><?php esc_html_e( 'すべて選択', 'kashiwazaki-seo-rich-preview-manager' ); ?></button>
						<button type="button" class="button button-small ksrpm-deselect-all-post-types"><?php esc_html_e( 'すべて解除', 'kashiwazaki-seo-rich-preview-manager' ); ?></button>
					</p>
					<fieldset class="ksrpm-post-types-grid">
						<?php foreach ( $post_types as $pt ) : ?>
							<label class="ksrpm-post-type-label">
								<input type="checkbox" class="ksrpm-post-type-checkbox" name="<?php echo esc_attr( $n ); ?>[enable_for_post_types][]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $selected_types, true ) ); ?> />
								<?php echo esc_html( $pt->label ); ?>
								<code><?php echo esc_html( $pt->name ); ?></code>
							</label>
						<?php endforeach; ?>
					</fieldset>
				</div>

				<?php submit_button( __( '設定を保存', 'kashiwazaki-seo-rich-preview-manager' ) ); ?>
			</form>

			<div class="ksrpm-footer">
				<strong>Kashiwazaki SEO Rich Preview Manager</strong> v<?php echo esc_html( KSRPM_VERSION ); ?>
				&mdash;
				<?php
				printf(
					esc_html__( 'Developed by %s', 'kashiwazaki-seo-rich-preview-manager' ),
					'<a href="https://www.tsuyoshikashiwazaki.jp" target="_blank">Tsuyoshi Kashiwazaki</a>'
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( $this->page_hook !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'ksrpm-admin-style',
			KSRPM_ASSETS_URL . 'css/admin-style.css',
			array(),
			KSRPM_VERSION
		);

		wp_enqueue_script(
			'ksrpm-admin-script',
			KSRPM_ASSETS_URL . 'js/admin-script.js',
			array( 'jquery', 'media-upload' ),
			KSRPM_VERSION,
			true
		);

		wp_localize_script(
			'ksrpm-admin-script',
			'ksrpmAdmin',
			array(
				'selectImage'  => __( 'Select Image', 'kashiwazaki-seo-rich-preview-manager' ),
				'useThisImage' => __( 'Use This Image', 'kashiwazaki-seo-rich-preview-manager' ),
				'homeUrl'      => home_url( '/' ),
			)
		);
	}

	/**
	 * Add settings link to plugin page.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=kashiwazaki-seo-rich-preview-manager' ),
			__( '設定', 'kashiwazaki-seo-rich-preview-manager' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ksrpm_settings_group-options' ) ) {
			add_settings_error( KSRPM_OPTION_NAME, 'nonce_failed', __( 'セキュリティチェックに失敗しました。', 'kashiwazaki-seo-rich-preview-manager' ), 'error' );
			return $this->options;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( KSRPM_OPTION_NAME, 'permission_denied', __( 'この設定を更新する権限がありません。', 'kashiwazaki-seo-rich-preview-manager' ), 'error' );
			return $this->options;
		}

		$sanitized = array();

		// Booleans.
		$sanitized['enable_ogp']              = ! empty( $input['enable_ogp'] );
		$sanitized['enable_twitter_card']     = ! empty( $input['enable_twitter_card'] );
		$sanitized['enable_meta_thumbnail']   = ! empty( $input['enable_meta_thumbnail'] );
		$sanitized['enable_pagemap']          = ! empty( $input['enable_pagemap'] );
		$sanitized['enable_robots_max_image'] = ! empty( $input['enable_robots_max_image'] );

		// Site name.
		$sanitized['site_name'] = isset( $input['site_name'] ) ? wp_strip_all_tags( sanitize_text_field( $input['site_name'] ) ) : '';

		// Default OG type.
		$allowed_og_types             = array( 'website', 'article', 'blog', 'product', 'video' );
		$og_type                      = isset( $input['default_og_type'] ) ? sanitize_text_field( $input['default_og_type'] ) : 'website';
		$sanitized['default_og_type'] = in_array( $og_type, $allowed_og_types, true ) ? $og_type : 'website';

		// Default Twitter Card type.
		$allowed_card_types                     = array( 'summary', 'summary_large_image' );
		$card_type                              = isset( $input['default_twitter_card_type'] ) ? sanitize_text_field( $input['default_twitter_card_type'] ) : 'summary_large_image';
		$sanitized['default_twitter_card_type'] = in_array( $card_type, $allowed_card_types, true ) ? $card_type : 'summary_large_image';

		// Facebook App ID.
		$fb_app_id = isset( $input['fb_app_id'] ) ? sanitize_text_field( trim( $input['fb_app_id'] ) ) : '';
		if ( ! empty( $fb_app_id ) && ! ctype_digit( $fb_app_id ) ) {
			add_settings_error( KSRPM_OPTION_NAME, 'invalid_fb_app_id', __( 'Facebook App IDは数字のみです。', 'kashiwazaki-seo-rich-preview-manager' ), 'warning' );
			$fb_app_id = '';
		}
		$sanitized['fb_app_id'] = $fb_app_id;

		// Twitter site.
		$twitter_site = isset( $input['twitter_site'] ) ? sanitize_text_field( trim( $input['twitter_site'] ) ) : '';
		$twitter_site = preg_replace( '/[^a-zA-Z0-9_]/', '', $twitter_site );
		if ( strlen( $twitter_site ) > 15 ) {
			$twitter_site = substr( $twitter_site, 0, 15 );
		}
		$sanitized['twitter_site'] = $twitter_site;

		// Default image URL.
		$image_url = isset( $input['default_image'] ) ? esc_url_raw( trim( $input['default_image'] ) ) : '';
		if ( ! empty( $image_url ) && ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			add_settings_error( KSRPM_OPTION_NAME, 'invalid_image_url', __( 'デフォルト画像のURLが無効です。', 'kashiwazaki-seo-rich-preview-manager' ), 'error' );
			$image_url = '';
		}
		$sanitized['default_image'] = $image_url;

		// Post types.
		if ( isset( $input['enable_for_post_types'] ) && is_array( $input['enable_for_post_types'] ) ) {
			$valid_post_types = get_post_types( array( 'public' => true ), 'names' );
			$validated        = array();
			foreach ( array_map( 'sanitize_text_field', $input['enable_for_post_types'] ) as $pt ) {
				if ( in_array( $pt, $valid_post_types, true ) ) {
					$validated[] = $pt;
				}
			}
			$sanitized['enable_for_post_types'] = ! empty( $validated ) ? array_values( array_unique( $validated ) ) : array( 'post', 'page' );
		} else {
			$sanitized['enable_for_post_types'] = array( 'post', 'page' );
		}

		return $sanitized;
	}

	/**
	 * Get current options.
	 *
	 * @return array Current options.
	 */
	public function get_options() {
		return $this->options;
	}
}
