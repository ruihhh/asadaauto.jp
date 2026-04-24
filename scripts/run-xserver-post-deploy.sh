#!/usr/bin/env bash

set -euo pipefail
trap 'status=$?; echo "run-xserver-post-deploy.sh failed at line ${LINENO}: ${BASH_COMMAND}" >&2; exit "${status}"' ERR

required_vars=(
    SSH_HOST
    SSH_USERNAME
    SSH_PRIVATE_KEY
    DEPLOY_LARAVEL_BASE_PATH
)

for var_name in "${required_vars[@]}"; do
    if [[ -z "${!var_name:-}" ]]; then
        echo "${var_name} is required." >&2
        exit 1
    fi
done

SSH_PORT="${SSH_PORT:-10022}"
SSH_PHP_BIN="${SSH_PHP_BIN:-php}"
SSH_COMPOSER_BIN="${SSH_COMPOSER_BIN:-composer}"
SSH_CONNECT_RETRIES="${SSH_CONNECT_RETRIES:-3}"
SSH_CONNECT_RETRY_DELAY="${SSH_CONNECT_RETRY_DELAY:-5}"
SSH_COMPOSER_MEMORY_LIMIT="${SSH_COMPOSER_MEMORY_LIMIT:--1}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-false}"
NORMALIZED_LARAVEL_BASE_PATH="${DEPLOY_LARAVEL_BASE_PATH%/}"

if [[ "${DEPLOY_LARAVEL_BASE_PATH}" != /* ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH must be an absolute path." >&2
    exit 1
fi

if [[ -z "${NORMALIZED_LARAVEL_BASE_PATH}" ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH cannot be '/'." >&2
    exit 1
fi

if [[ "${RUN_MIGRATIONS}" != "true" && "${RUN_MIGRATIONS}" != "false" ]]; then
    echo "RUN_MIGRATIONS must be either true or false." >&2
    exit 1
fi

if [[ ! "${SSH_CONNECT_RETRIES}" =~ ^[0-9]+$ ]]; then
    echo "SSH_CONNECT_RETRIES must be a positive integer." >&2
    exit 1
fi

if [[ ! "${SSH_CONNECT_RETRY_DELAY}" =~ ^[0-9]+$ ]]; then
    echo "SSH_CONNECT_RETRY_DELAY must be a non-negative integer." >&2
    exit 1
fi

SSH_CONNECT_RETRIES=$((10#${SSH_CONNECT_RETRIES}))
SSH_CONNECT_RETRY_DELAY=$((10#${SSH_CONNECT_RETRY_DELAY}))

if ((SSH_CONNECT_RETRIES < 1)); then
    echo "SSH_CONNECT_RETRIES must be a positive integer." >&2
    exit 1
fi

SSH_DIR="${HOME}/.ssh"
SSH_KEY_PATH="${RUNNER_TEMP:-/tmp}/xserver_deploy_key"

mkdir -p "${SSH_DIR}"
chmod 700 "${SSH_DIR}"

printf '%s\n' "${SSH_PRIVATE_KEY}" > "${SSH_KEY_PATH}"
chmod 600 "${SSH_KEY_PATH}"

ssh-keyscan -p "${SSH_PORT}" -H "${SSH_HOST}" >> "${SSH_DIR}/known_hosts"

quote() {
    printf '%q' "$1"
}

remote_env=(
    "DEPLOY_LARAVEL_BASE_PATH=$(quote "${NORMALIZED_LARAVEL_BASE_PATH}")"
    "SSH_PHP_BIN=$(quote "${SSH_PHP_BIN}")"
    "SSH_COMPOSER_BIN=$(quote "${SSH_COMPOSER_BIN}")"
    "COMPOSER_MEMORY_LIMIT=$(quote "${SSH_COMPOSER_MEMORY_LIMIT}")"
    "RUN_MIGRATIONS=$(quote "${RUN_MIGRATIONS}")"
)

echo "Running post-deploy commands on ${SSH_USERNAME}@${SSH_HOST}:${SSH_PORT}"
echo "Laravel path: ${NORMALIZED_LARAVEL_BASE_PATH}"
echo "Migrations: ${RUN_MIGRATIONS}"

ssh_options=(
    -i "${SSH_KEY_PATH}"
    -p "${SSH_PORT}"
    -o BatchMode=yes
    -o ConnectTimeout=20
    -o ConnectionAttempts=3
    -o IdentitiesOnly=yes
    -o PreferredAuthentications=publickey
    -o ServerAliveInterval=30
    -o ServerAliveCountMax=3
    -o StrictHostKeyChecking=yes
)

run_remote_script() {
    ssh \
        "${ssh_options[@]}" \
        "${SSH_USERNAME}@${SSH_HOST}" \
        "${remote_env[*]} bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail
trap 'status=$?; echo "[post-deploy] failed at remote line ${LINENO}: ${BASH_COMMAND}" >&2; exit "${status}"' ERR

log() {
    printf '[post-deploy] %s\n' "$*"
}

require_command() {
    local command_name="$1"

    if [[ "${command_name}" == */* ]]; then
        if [[ ! -x "${command_name}" ]]; then
            echo "[post-deploy] command was not found or is not executable: ${command_name}" >&2
            exit 127
        fi

        return
    fi

    if ! command -v "${command_name}" >/dev/null 2>&1; then
        echo "[post-deploy] command was not found in PATH: ${command_name}" >&2
        exit 127
    fi
}

require_writable_dir() {
    local directory="$1"
    local test_file

    mkdir -p "${directory}"

    if [[ ! -d "${directory}" ]]; then
        echo "[post-deploy] required directory could not be created: ${directory}" >&2
        exit 1
    fi

    test_file="${directory}/.deploy-write-test-$$"

    if ! touch "${test_file}" 2>/dev/null; then
        echo "[post-deploy] required directory is not writable: ${directory}" >&2
        exit 1
    fi

    rm -f "${test_file}"
}

cd "${DEPLOY_LARAVEL_BASE_PATH}"
log "Working directory: $(pwd)"

if [[ ! -f composer.json ]]; then
    echo "composer.json was not found in ${DEPLOY_LARAVEL_BASE_PATH}." >&2
    exit 1
fi

require_command "${SSH_PHP_BIN}"
require_command "${SSH_COMPOSER_BIN}"

log "PHP: $("${SSH_PHP_BIN}" -r 'echo PHP_VERSION;' 2>/dev/null)"
log "Composer: $("${SSH_COMPOSER_BIN}" --version --no-interaction 2>/dev/null)"

log "Ensuring Laravel writable directories exist."
for writable_dir in \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs
do
    require_writable_dir "${writable_dir}"
done

if [[ ! -f .env ]]; then
    echo "[post-deploy] .env was not found. Copy .env.xserver.example to .env on the server, configure production values, run php artisan key:generate --force, then rerun deploy." >&2
    exit 1
fi

app_key="$(sed -n 's/^[[:space:]]*APP_KEY[[:space:]]*=[[:space:]]*//p' .env | tail -n 1)"
app_key="${app_key%$'\r'}"

if [[ -z "${app_key}" || "${app_key}" == '""' || "${app_key}" == "''" ]]; then
    echo "[post-deploy] APP_KEY is empty in .env. Run: cd ${DEPLOY_LARAVEL_BASE_PATH} && ${SSH_PHP_BIN} artisan key:generate --force" >&2
    exit 1
fi

if ! APP_KEY_VALUE="${app_key}" "${SSH_PHP_BIN}" -r '
$key = trim((string) getenv("APP_KEY_VALUE"));

if (
    strlen($key) >= 2
    && (($key[0] === "\"" && substr($key, -1) === "\"") || ($key[0] === "'"'"'" && substr($key, -1) === "'"'"'"))
) {
    $key = substr($key, 1, -1);
}

if (str_starts_with($key, "base64:")) {
    $decoded = base64_decode(substr($key, 7), true);

    if ($decoded === false) {
        exit(2);
    }

    $key = $decoded;
}

exit(strlen($key) === 32 ? 0 : 3);
'; then
    echo "[post-deploy] APP_KEY in .env is not valid for AES-256-CBC. Run: cd ${DEPLOY_LARAVEL_BASE_PATH} && ${SSH_PHP_BIN} artisan key:generate --force" >&2
    exit 1
fi

log "Installing PHP dependencies."
"${SSH_COMPOSER_BIN}" install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

if [[ ! -f vendor/autoload.php ]]; then
    echo "vendor/autoload.php was not generated." >&2
    exit 1
fi

if [[ "${RUN_MIGRATIONS}" == "true" ]]; then
    log "Running migrations."
    "${SSH_PHP_BIN}" artisan migrate --force
else
    log "Skipping migrations. Set RUN_MIGRATIONS=true to run them during deploy."
fi

log "Optimizing Laravel caches."
"${SSH_PHP_BIN}" artisan optimize
log "Post-deploy commands completed."
REMOTE_SCRIPT
}

attempt=1

while true; do
    if run_remote_script; then
        break
    fi

    status=$?

    if [[ "${status}" -ne 255 || "${attempt}" -ge "${SSH_CONNECT_RETRIES}" ]]; then
        echo "Post-deploy SSH command failed with status ${status}." >&2
        exit "${status}"
    fi

    echo "SSH session ended with status 255; retrying in ${SSH_CONNECT_RETRY_DELAY}s (${attempt}/${SSH_CONNECT_RETRIES})." >&2
    sleep "${SSH_CONNECT_RETRY_DELAY}"
    attempt=$((attempt + 1))
done
