# Xserverデプロイ手順 (Laravel 12)

## 1. 配置構成

Xserver共有レンタルサーバーでは、アプリ本体をWeb公開領域の外に置く構成が安全です。

- Laravel本体: `/home/<account>/laravel_app`
- Web公開領域: `/home/<account>/<domain>/public_html`

`public_html`にはこのリポジトリの `deploy/xserver/public_html` 配下を置きます。

## 2. アップロード

1. リポジトリ全体を`laravel_app`へアップロード
2. `deploy/xserver/public_html/index.php` を `public_html/index.php` にコピー
3. `deploy/xserver/public_html/.htaccess` を `public_html/.htaccess` にコピー
4. `public_html/index.php`の`$laravelBasePath`を実際の絶対パスに修正

## 3. SSHで初期化

```bash
cd /home/<account>/laravel_app
cp .env.xserver.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=CarSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

`storage:link`が共有サーバー制限で失敗する場合は、公開が必要なファイルを`public/storage`へ同期する運用に切り替えてください。

## 4. Cron設定

XserverのCronに1分間隔で以下を登録します。

```cron
* * * * * /path/to/php /home/<account>/laravel_app/artisan schedule:run >> /dev/null 2>&1
```

- `/path/to/php` はXserverのサーバー情報ページまたは `which php` で確認した実パスを指定
- 本プロジェクトは `routes/console.php` で `queue:work --stop-when-empty` を毎分起動するため、常駐プロセス不要でジョブ処理可能

## 5. 更新デプロイ

```bash
cd /home/<account>/laravel_app
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

フロント資産を更新した場合は、`public/build`を含めてアップロードしてください。

## 6. GitHub Actions で main push 時に FTP 自動アップロード

このリポジトリには `main` ブランチへの push を契機に、GitHub Actions から FTP へ自動アップロードする workflow を追加しています。

- Workflow: `.github/workflows/deploy-xserver-ftp.yml`
- 配布準備スクリプト: `scripts/prepare-xserver-release.sh`
- 診断スクリプト: `scripts/debug-xserver-release.sh`

### 6-1. GitHub 側で設定する値

GitHub の `Settings > Secrets and variables > Actions` で、以下を設定してください。

Secrets:

- `FTP_SERVER`: FTP ホスト名
- `FTP_USERNAME`: FTP ユーザー名
- `FTP_PASSWORD`: FTP パスワード

Variables:

- `DEPLOY_LARAVEL_BASE_PATH`: Xserver 上の Laravel 本体配置先
  - 例: `/home/<account>/laravel_app`
- `FTP_LARAVEL_APP_DIR`: Laravel 本体アップロード先
  - 例: `/home/<account>/laravel_app/`
- `FTP_PUBLIC_HTML_DIR`: 公開ディレクトリアップロード先
  - 例: `/home/<account>/<domain>/public_html/`
- `FTP_PORT`: 任意。未設定時は `21`
- `FTP_PROTOCOL`: 任意。未設定時は `ftp`
  - `ftps` が使える契約なら `ftps` を推奨

`environment: production` を指定しているため、必要であれば GitHub の Environment `production` にこれらを登録して保護ルールを付けられます。repo-level の Secrets / Variables でも動作します。

### 6-2. workflow の動き

1. `main` への push で起動
2. `composer install --no-dev` を実行
3. `npm ci && npm run build` で Vite アセットを生成
4. `.deploy/laravel_app` と `.deploy/public_html` を組み立て
5. FTP でそれぞれのディレクトリに同期

`public_html` 側には以下が含まれます。

- `public/` 配下の公開アセット
- `public/build/` のビルド成果物
- `deploy/xserver/public_html/index.php`
- `deploy/xserver/public_html/.htaccess`

その際、`index.php` 内の `$laravelBasePath` は `DEPLOY_LARAVEL_BASE_PATH` の値で自動置換されます。

### 6-3. 補足

- `push` トリガーなので、Pull Request を `main` にマージした場合も自動で実行されます
- DB マイグレーションや `php artisan optimize` は FTP だけでは実行できないため、必要時はサーバー側で別途実行してください
- `public/storage` はサーバー側の `php artisan storage:link` を維持する前提で、自動アップロード対象から外しています
- GitHub Actions 上で失敗箇所が分かりにくい場合は、`Run deployment diagnostics` のログを確認してください。FTP パスワードなどは表示せず、変数・テンプレート・生成物の状態だけを出します
