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
- サーバー側 post-deploy スクリプト: `scripts/run-xserver-post-deploy.sh`

### 6-1. GitHub 側で設定する値

GitHub の `Settings > Secrets and variables > Actions` で、以下を設定してください。

Secrets:

- `FTP_SERVER`: FTP ホスト名
- `FTP_USERNAME`: FTP ユーザー名
- `FTP_PASSWORD`: FTP パスワード
- `SSH_PRIVATE_KEY`: Xserver に登録した公開鍵に対応する秘密鍵
- `SSH_HOST`: 任意。未設定時は `FTP_SERVER` を使用
- `SSH_USERNAME`: 任意。未設定時は `FTP_USERNAME` を使用

Variables:

- `DEPLOY_LARAVEL_BASE_PATH`: Xserver 上の Laravel 本体配置先
  - 例: `/home/<account>/laravel_app`
- `FTP_LARAVEL_APP_DIR`: Laravel 本体アップロード先
  - 例: `/home/<account>/laravel_app/`
- `FTP_PUBLIC_HTML_DIR`: 公開ディレクトリアップロード先
  - 例: `/home/<account>/<domain>/public_html/`
  - この環境では `public_html/b-2026.asadaauto.jp/`
- `FTP_PORT`: 任意。未設定時は `21`
- `FTP_PROTOCOL`: 任意。未設定時は `ftp`
  - `ftps` が使える契約なら `ftps` を推奨
- `SSH_PORT`: 任意。未設定時は `10022`
- `SSH_PHP_BIN`: 任意。未設定時は `php`
- `SSH_COMPOSER_BIN`: 任意。未設定時は `composer`
- `SSH_CONNECT_RETRIES`: 任意。SSH セッションが切断された場合の再試行回数。未設定時は `3`
- `SSH_CONNECT_RETRY_DELAY`: 任意。SSH 再試行前の待機秒数。未設定時は `5`
- `SSH_COMPOSER_MEMORY_LIMIT`: 任意。サーバー上で Composer 実行時に渡す `COMPOSER_MEMORY_LIMIT`。未設定時は `-1`
- `RUN_MIGRATIONS`: 任意。`true` の場合のみデプロイ時に `php artisan migrate --force` を実行。未設定時は `false`

`environment: production` を指定しているため、必要であれば GitHub の Environment `production` にこれらを登録して保護ルールを付けられます。repo-level の Secrets / Variables でも動作します。

### 6-2. workflow の動き

1. `main` への push で起動
2. `npm ci && npm run build` で Vite アセットを生成
3. `.deploy/laravel_app` と `.deploy/public_html` を組み立て
4. `vendor/`, `.env`, `storage/` を除外して Laravel 本体を FTP 同期
5. `public_html` を FTP 同期
6. SSH でサーバーに接続し、Laravel 本体ディレクトリで `composer install --no-dev --optimize-autoloader` を実行

`public_html` 側には以下が含まれます。

- `public/` 配下の公開アセット
- `public/build/` のビルド成果物
- `deploy/xserver/public_html/index.php`
- `deploy/xserver/public_html/.htaccess`

その際、`index.php` 内の `$laravelBasePath` は `DEPLOY_LARAVEL_BASE_PATH` の値で自動置換されます。

`laravel_app/public` も Laravel 本体側に残します。これは公開用ではなく、Laravel の Vite ヘルパーが `public/build/manifest.json` を参照するために必要です。実際にブラウザから公開されるファイルは `FTP_PUBLIC_HTML_DIR` 側にアップロードされます。

### 6-3. 補足

- `push` トリガーなので、Pull Request を `main` にマージした場合も自動で実行されます
- DB マイグレーションは `RUN_MIGRATIONS=true` にした場合だけ自動実行します。通常は安全のため `false` のままにしてください
- `public/storage` はサーバー側の `php artisan storage:link` を維持する前提で、自動アップロード対象から外しています
- GitHub Actions 上で失敗箇所が分かりにくい場合は、`Run deployment diagnostics` のログを確認してください。FTP パスワードなどは表示せず、変数・テンプレート・生成物の状態だけを出します。診断用の生成物は runner の一時ディレクトリに作成し、FTP アップロード対象には含めません

### 6-4. Xserver の SSH 設定

Xserver の SSH は公開鍵認証です。サーバーパネルの SSH 設定で公開鍵を登録し、対応する秘密鍵を GitHub Secret `SSH_PRIVATE_KEY` に登録してください。

Xserver の SSH ポートは通常 `10022` です。違うポートを使う場合のみ `SSH_PORT` を変更してください。

GitHub Actions から非対話で接続するため、`SSH_PRIVATE_KEY` にはパスフレーズなしのデプロイ用秘密鍵を登録してください。パスフレーズ付き鍵を使う場合は、別途 ssh-agent と passphrase 対応が必要です。

GitHub Hosted Runner から SSH 接続する場合、Xserver 側で「国外IPアドレスからのアクセス制限」を有効にしていると接続できない可能性があります。その場合は、制限設定を見直すか、日本国内の self-hosted runner などから実行してください。

### 6-5. SSH post-deploy が `Connection closed` で失敗する場合

`Install PHP dependencies on server` ステップで `Connection closed by <host> port 10022` とだけ表示される場合、SSH セッションがリモートコマンドの途中または開始直後に切断されています。workflow は自動で 3 回まで再試行し、次回ログには `[post-deploy] Installing PHP dependencies.` のようにリモート側の進行状況が表示されます。

- `[post-deploy]` のログが 1 行も出ない場合: Xserver の SSH 有効化、公開鍵登録、国外 IP アクセス制限、`SSH_HOST` / `SSH_USERNAME` / `SSH_PORT` を確認してください
- `command was not found in PATH` が出る場合: `SSH_PHP_BIN` または `SSH_COMPOSER_BIN` に Xserver 上の絶対パスを設定してください
- `Installing PHP dependencies` の後に切断される場合: Composer 実行中に切断されている可能性があります。必要に応じて `SSH_COMPOSER_MEMORY_LIMIT` を調整してください
