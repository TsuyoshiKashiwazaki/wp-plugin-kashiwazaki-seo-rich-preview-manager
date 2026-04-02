# Changelog

このプロジェクトのすべての注目すべき変更はこのファイルに記録されます。

フォーマットは [Keep a Changelog](https://keepachangelog.com/ja/1.0.0/) に基づいており、
[セマンティック バージョニング](https://semver.org/lang/ja/) に準拠しています。

## [2.0.0] - 2026-04-02 — Kashiwazaki SEO OGP Manager から改名・機能拡張

### Added
- プラグイン名を Kashiwazaki SEO OGP Manager → Kashiwazaki SEO Rich Preview Manager に変更
- Meta Thumbnail (`<meta name="thumbnail">`) 出力機能を追加
- PageMap (HTMLコメント形式) 出力機能を追加
- 全5機能（OGP / Twitter Card / Meta Thumbnail / PageMap / Robots）の一括重複チェック機能を追加
- 旧プラグイン（Kashiwazaki SEO OGP Manager）の自動無効化＆管理画面通知機能を追加
- 旧プラグインからのオプション・post meta 自動マイグレーション機能を追加
- `ksrpm_enable_meta_thumbnail_output` フィルターを追加
- `ksrpm_enable_pagemap_output` フィルターを追加
- `ksrpm_enable_robots_max_image_output` フィルターを追加
- `ksrpm_meta_thumbnail_url` フィルターを追加
- `ksrpm_pagemap_data` フィルターを追加

### Changed
- 設定画面をカード形式のUIに刷新（トグルスイッチ、SNS / SERP / AIO バッジ）
- Robots max-image-preview 出力をOGPブロックから独立した出力ブロックに分離
- Robots max-image-preview のデフォルトをOFFに変更（WordPress 5.7以降はWP本体が自動出力するため）
- 設定リンクのURLを修正（add_menu_page対応）
- 全クラスプレフィックスを `KSOM_` → `KSRPM_` に変更
- 全フィルター / アクションフックプレフィックスを `ksom_` → `ksrpm_` に変更
- オプション名を `ksom_options` → `ksrpm_options` に変更
- post metaキープレフィックスを `_ksom_` → `_ksrpm_` に変更

## [1.0.0] - 2025-01-13 — Kashiwazaki SEO OGP Manager として初回リリース

### Added
- OGP（Open Graph Protocol）タグ出力機能
- Twitter Card メタタグ出力機能
- 投稿タイプごとのOGP出力制御
- 投稿編集画面のOGP Settingsメタボックス（タイトル、説明、画像、タイプ）
- OGP画像の自動選択（カスタム画像 → アイキャッチ画像 → デフォルト画像）
- OGP画像の width / height 自動出力
- article:タグの動的出力（published_time, modified_time, author, section, tag）
- Robots Meta（max-image-preview:large）出力機能
- Facebook App ID 対応
- Twitter ユーザー名（twitter:site）対応
- デフォルトOGP画像設定
- 投稿タイプ一括選択機能
- 日本語ファイル名画像のURLエンコード対応
- 管理画面の日本語化

[2.0.0]: https://github.com/TsuyoshiKashiwazaki/wp-plugin-kashiwazaki-seo-rich-preview-manager/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/TsuyoshiKashiwazaki/wp-plugin-kashiwazaki-seo-rich-preview-manager/releases/tag/v1.0.0
