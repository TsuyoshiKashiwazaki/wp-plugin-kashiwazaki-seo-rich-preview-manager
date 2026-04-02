=== Kashiwazaki SEO Rich Preview Manager ===
Contributors: tsuyoshikashiwazaki
Tags: ogp, open graph, twitter cards, pagemap, meta thumbnail, seo, rich preview, ai overviews
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

OGP・Twitter Card・PageMap・Meta Thumbnailを一元管理し、SERP・AIO・SNSでのリッチプレビュー表示を最適化するプラグイン。

== Description ==

Kashiwazaki SEO Rich Preview Managerは、WordPressサイトのリッチプレビュー表示を一元管理できる高機能プラグインです。

= 出力機能（個別にON/OFF可能） =

* OGP（Open Graph Protocol） — Facebook・LINEなどでのシェア表示を制御
* Twitter Card — X (Twitter) でのカード表示を制御
* Meta Thumbnail — Google検索結果やAI Overviewsのサムネイル候補
* PageMap — Google Programmable Search Engine等で使用されるサムネイルデータ
* Robots max-image-preview — Google検索結果での大きい画像プレビュー許可（WP本体と重複するため通常OFF）

= 管理機能 =

* 投稿タイプごとの出力制御
* 投稿編集画面でのOGP個別設定（タイトル、説明、画像、タイプ）
* 一括重複チェック（全5機能の他プラグイン/WP本体との競合を一括検出）
* 旧プラグイン（kashiwazaki-seo-ogp-manager）の自動無効化＆データ移行
* カード形式のわかりやすい設定UI

= SERP・AIO対応 =

PageMapとMeta Thumbnailタグを出力することで、Google検索結果やAI Overviewsでのアイキャッチ画像表示を最適化します。

= SNSシェア最適化 =

* Facebook：Open Graph Protocol準拠
* Twitter：Twitter Cards対応（summary、summary_large_image）
* LINE：OGP画像とタイトルを最適化

== Installation ==

1. プラグインをアップロードして有効化
2. 管理メニューの「Kashiwazaki SEO Rich Preview Manager」から基本設定を行う
3. 各投稿の編集画面で個別のOGP設定が可能

== Frequently Asked Questions ==

= OGP画像を設定しない場合はどうなりますか？ =

アイキャッチ画像が自動的に使用されます。アイキャッチ画像も設定されていない場合は、デフォルト画像が使用されます。

= Meta ThumbnailやPageMapの画像はどう決まりますか？ =

OGP画像と同じ優先順序で決まります：OGP Settingsの個別画像 → アイキャッチ画像 → デフォルト画像。

= 旧プラグイン（kashiwazaki-seo-ogp-manager）からの移行は？ =

旧プラグインが有効な状態で本プラグインを有効化すると、旧プラグインを自動無効化し、設定とpost metaを自動移行します。手動の作業は不要です。

= Robots max-image-previewはONにすべきですか？ =

WordPress 5.7以降はWP本体が同じタグを自動出力しているため、通常はOFFのままで問題ありません。一括重複チェックで確認できます。

== Screenshots ==

1. 管理画面の設定ページ（カード形式UI）
2. 投稿編集画面のメタボックス
3. 一括重複チェック結果

== Changelog ==

= 2.0.0 = Kashiwazaki SEO OGP Manager から改名・機能拡張
* プラグイン名を Kashiwazaki SEO OGP Manager → Kashiwazaki SEO Rich Preview Manager に変更
* Meta Thumbnail 出力機能を追加
* PageMap 出力機能を追加
* 全5機能の一括重複チェック機能を追加
* 旧プラグインの自動無効化＆通知機能を追加
* 旧プラグインからのオプション・post meta 自動マイグレーションを追加
* 設定画面をカード形式UIに刷新（トグルスイッチ、SNS/SERP/AIOバッジ）
* Robots max-image-preview 出力をOGPブロックから独立分離
* Robots max-image-preview のデフォルトをOFFに変更
* 設定リンクのURL修正
* メニュー名を「Kashiwazaki SEO Rich Preview Manager」に変更

= 1.0.0 = Kashiwazaki SEO OGP Manager として初回リリース
* 基本的なOGP機能
* Twitter Cards対応
* 投稿ごとのメタボックス
* 管理画面設定ページ
* 日本語ファイル名画像のURLエンコード対応

== Upgrade Notice ==

= 2.0.0 =
Kashiwazaki SEO OGP Manager からの改名。Meta Thumbnail・PageMap出力、一括重複チェック、カード形式UI、旧プラグイン自動移行に対応。
