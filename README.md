# Kashiwazaki SEO Rich Preview Manager

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.0.0-orange.svg)](https://github.com/TsuyoshiKashiwazaki/wp-plugin-kashiwazaki-seo-rich-preview-manager/releases)

OGP・Twitter Card・PageMap・Meta Thumbnailを一元管理し、SERP・AIO・SNSでのリッチプレビュー表示を最適化するプラグイン。

**特徴**: 投稿タイプごとに細かく制御でき、OGP画像のwidth/heightも自動出力。Google AI OverviewsやSERPsでのアイキャッチ表示にも対応。

## 主な機能

### 出力機能（個別にON/OFF可能）

- **OGP（Open Graph Protocol）** — Facebook・LINEなどでシェアされた際のタイトル・画像・説明を制御する og:* メタタグ
- **Twitter Card** — X (Twitter) でシェアされた際のカード表示を制御する twitter:* メタタグ
- **Meta Thumbnail** — Google検索結果やAI Overviewsのサムネイル候補となる `<meta name="thumbnail">` タグ
- **PageMap** — Google Programmable Search Engine等で使用されるPageMap thumbnail DataObject（HTMLコメント形式）
- **Robots max-image-preview** — Google検索結果で大きい画像プレビューを許可するメタタグ（WP 5.7+は本体が自動出力するため通常はOFF）

### 管理機能

- 投稿タイプごとの出力制御（有効化する投稿タイプを選択可能）
- 個別投稿でのカスタム設定（OGP Settings メタボックス：タイトル、説明、画像、タイプ）
- OGP画像の自動選択（OGP Settingsの個別画像 → アイキャッチ画像 → デフォルト画像）
- 画像サイズ（width/height）の自動出力
- article:タグの自動出力（記事タイプの場合）
- 一括重複チェック（全5機能について、他のプラグインやWP本体との重複を一括検出）
- 旧プラグイン（kashiwazaki-seo-ogp-manager）の自動無効化＆データ移行
- Facebook App ID / X (Twitter) ユーザー名の設定

## インストール

1. このリポジトリをクローンまたはダウンロード
2. `kashiwazaki-seo-rich-preview-manager` フォルダを `/wp-content/plugins/` にアップロード
3. WordPressの管理画面からプラグインを有効化
4. 管理メニューの「Kashiwazaki SEO Rich Preview Manager」から設定

## 設定画面

設定画面はカード形式のUIで構成されています：

### 出力機能カード
各機能（OGP / Twitter Card / Meta Thumbnail / PageMap / Robots）をトグルスイッチでON/OFF。各機能にSNS/SERP/AIOのバッジが付いており、用途が一目でわかります。「一括重複チェック」ボタンで全機能の重複状態を一覧表示できます。

### OGP詳細設定カード
サイト名、デフォルト og:type、デフォルト Twitter Card タイプの設定。

### SNSアカウントカード
X (Twitter) ユーザー名（@なしで入力、自動付与）、Facebook App ID。

### デフォルト画像カード
アイキャッチ画像がない投稿で使用されるフォールバック画像。推奨サイズ: 1200 x 630px。

### 対象投稿タイプカード
リッチプレビュータグ出力とメタボックス表示を有効にする投稿タイプを選択。

## 投稿編集画面

各投稿・固定ページの編集画面に「OGP Settings」メタボックスが表示されます：

- **OG Title** — 空欄の場合は投稿タイトルを使用（推奨: 60-90文字）
- **OG Description** — 空欄の場合は抜粋またはコンテンツから自動生成（推奨: 150-200文字）
- **OG Image** — 空欄の場合はアイキャッチ画像 → デフォルト画像の順で決定（推奨: 1200x630px）
- **OG Type** — article / website / blog / product / video
- **Twitter Card Type** — summary / summary_large_image

## 技術仕様

### システム要件

- WordPress 6.0以上
- PHP 7.4以上

### 出力されるデータ

**OGP基本タグ:**
- `og:site_name`, `og:locale`, `og:type`, `og:title`, `og:description`, `og:url`
- `og:image`, `og:image:width`, `og:image:height`
- `fb:app_id`（設定時）

**記事タイプの場合は追加:**
- `article:published_time`, `article:modified_time`, `article:author`, `article:section`, `article:tag`

**Twitter Card:**
- `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`, `twitter:site`

**Meta Thumbnail:**
- `<meta name="thumbnail" content="画像URL">`

**PageMap（HTMLコメント形式）:**
```html
<!--
<PageMap>
  <DataObject type="thumbnail">
    <Attribute name="src" value="画像URL"/>
    <Attribute name="width" value="1200"/>
    <Attribute name="height" value="630"/>
  </DataObject>
</PageMap>
-->
```

**Robots（オプション）:**
- `<meta name="robots" content="max-image-preview:large">`

### フィルターフック

| フィルター | 説明 |
|---|---|
| `ksrpm_ogp_tags` | OGPタグ配列のカスタマイズ |
| `ksrpm_twitter_card_tags` | Twitter Cardタグ配列のカスタマイズ |
| `ksrpm_og_image` | OGP画像URLのカスタマイズ |
| `ksrpm_og_image_dimensions` | 画像サイズのカスタマイズ |
| `ksrpm_meta_thumbnail_url` | Meta Thumbnail画像URLのカスタマイズ |
| `ksrpm_pagemap_data` | PageMapデータのカスタマイズ |
| `ksrpm_valid_image_extensions` | 有効な画像拡張子のカスタマイズ |
| `ksrpm_enable_ogp_output` | OGP出力の有効/無効（ページ単位） |
| `ksrpm_enable_twitter_card_output` | Twitter Card出力の有効/無効（ページ単位） |
| `ksrpm_enable_meta_thumbnail_output` | Meta Thumbnail出力の有効/無効（ページ単位） |
| `ksrpm_enable_pagemap_output` | PageMap出力の有効/無効（ページ単位） |
| `ksrpm_enable_robots_max_image_output` | Robots出力の有効/無効（ページ単位） |

### Kashiwazaki SEO OGP Manager（v1.x）からの移行

移行は自動的に行われます：

- Kashiwazaki SEO OGP Manager が有効な状態で本プラグインを有効化すると、自動的に無効化
- `ksom_options` → `ksrpm_options` にオプションを移行
- post metaキー（`_ksom_*` → `_ksrpm_*`）を自動変換
- 管理画面に移行完了の通知を表示
- 手動の作業は不要です

## ライセンス

GPL-2.0-or-later

## サポート・開発者

**開発者**: 柏崎剛 (Tsuyoshi Kashiwazaki)
**ウェブサイト**: https://www.tsuyoshikashiwazaki.jp/
**サポート**: プラグインに関するご質問や不具合報告は、開発者ウェブサイトまでお問い合わせください。

## 貢献

バグ報告や機能リクエストは、GitHubのIssuesページからお願いします。

プルリクエストも歓迎します：
1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

---

<div align="center">

**Keywords**: WordPress, OGP, Open Graph Protocol, Twitter Card, PageMap, Meta Thumbnail, SEO, Rich Preview, AI Overviews, SERP

Made by [Tsuyoshi Kashiwazaki](https://github.com/TsuyoshiKashiwazaki)

</div>
