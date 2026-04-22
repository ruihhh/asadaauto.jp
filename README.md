# asadaauto.jp (Laravel 12 / Xserver対応)

Xserver共有レンタルサーバーで運用できる前提で作成した、中古車販売店向けのLaravel 12スターターです。

## 技術構成

- PHP: 8.5系
- Framework: Laravel 12
- DB: MariaDB 10.5系 (Xserver標準)
- 非同期処理: `database queue` + Cron経由スケジューラ
- フロント: Blade + 素のCSS (`public/css/site.css`) + Tailwind CSS (管理画面)
- 認証: Laravel Breeze

## 実装済み機能

### 公開サイト
- 在庫一覧ページ (`/`, `/cars`)
- 絞り込み: キーワード / メーカー / ボディタイプ / 価格帯 / 走行距離
- ソート: 新着 / 価格 / 走行距離 / 年式
- 車両詳細ページ (`/cars/{car}`) — 画像・スペック表示
- 問い合わせフォーム (`/contact`) — 送信時に管理者メール通知

### 管理画面 (`/admin/cars`)
- 車両CRUD（登録 / 編集 / 削除）
- 車両画像アップロード（JPEG / PNG / WebP、最大5MB）
- CSV一括エクスポート（Excel対応UTF-8 BOM付き）
- **管理者ユーザーのみアクセス可能**（`is_admin = true`）

### 認証・ユーザー管理
- ログイン / ユーザー登録 / パスワードリセット / メール認証
- プロフィール編集・アカウント削除
- ユーザーロール管理（`is_admin` フラグ）

## 主要ファイル

| 役割 | パス |
|------|------|
| 公開在庫一覧コントローラー | `app/Http/Controllers/CarController.php` |
| 管理車両コントローラー | `app/Http/Controllers/Admin/CarController.php` |
| 問い合わせコントローラー | `app/Http/Controllers/ContactController.php` |
| 管理者ミドルウェア | `app/Http/Middleware/EnsureUserIsAdmin.php` |
| Car モデル | `app/Models/Car.php` |
| User モデル | `app/Models/User.php` |
| ルーティング | `routes/web.php` |
| 公開一覧ビュー | `resources/views/cars/index.blade.php` |
| 公開詳細ビュー | `resources/views/cars/show.blade.php` |
| 管理一覧ビュー | `resources/views/admin/cars/index.blade.php` |
| Xserver用公開テンプレート | `deploy/xserver/public_html/` |
| デプロイ手順 | `docs/xserver-deploy.md` |

## ローカル起動手順 (Docker Compose)

```bash
# 初回のみ
cp .env.docker.example .env

# 起動 (app + scheduler + mariadb)
docker compose up --build -d

# DBマイグレーション & サンプルデータ投入
docker compose exec app php artisan migrate --seed

# 画像ストレージのシンボリックリンク作成
docker compose exec app php artisan storage:link
```

ブラウザ: `http://localhost:8000`
DB接続: `127.0.0.1:33060` (`asadaauto` / `asadaauto`)

停止:

```bash
docker compose down
```

テスト:

```bash
docker compose exec app php artisan test
```

## 管理者ユーザーの作成

管理画面にアクセスするには `is_admin = true` のユーザーが必要です。

```bash
docker compose exec app php artisan tinker
# Tinker 内で実行:
# \App\Models\User::factory()->admin()->create(['email' => 'admin@example.com', 'password' => bcrypt('password')]);
```

## 画像ストレージ

車両画像は `storage/app/public/cars/` に保存されます。
`php artisan storage:link` によって `public/storage` へのシンボリックリンクが作成され、公開URLでアクセス可能になります。

## Xserverデプロイ

詳細は `docs/xserver-deploy.md` を参照してください。

要点:

1. Laravel本体は `public_html` の外に配置
2. `public_html` には `deploy/xserver/public_html` の `index.php` と `.htaccess` を配置
3. `index.php` の `$laravelBasePath` を本番絶対パスに変更
4. Cronに `schedule:run` を1分間隔で登録
5. 本番環境でも `php artisan storage:link` を実行

## キュー実行方式

共有サーバーで常駐ワーカーを使わないため、`routes/console.php` に次を設定済みです。

- `queue:work --stop-when-empty --tries=3 --max-time=55` を毎分実行

これによりCronの `php artisan schedule:run` だけでジョブ処理できます。
