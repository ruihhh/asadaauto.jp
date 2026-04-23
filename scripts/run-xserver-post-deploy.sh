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
    "RUN_MIGRATIONS=$(quote "${RUN_MIGRATIONS}")"
)

echo "Running post-deploy commands on ${SSH_USERNAME}@${SSH_HOST}:${SSH_PORT}"
echo "Laravel path: ${NORMALIZED_LARAVEL_BASE_PATH}"
echo "Migrations: ${RUN_MIGRATIONS}"

ssh \
    -i "${SSH_KEY_PATH}" \
    -p "${SSH_PORT}" \
    -o IdentitiesOnly=yes \
    -o StrictHostKeyChecking=yes \
    "${SSH_USERNAME}@${SSH_HOST}" \
    "${remote_env[*]} bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail

cd "${DEPLOY_LARAVEL_BASE_PATH}"

if [[ ! -f composer.json ]]; then
    echo "composer.json was not found in ${DEPLOY_LARAVEL_BASE_PATH}." >&2
    exit 1
fi

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

if [[ ! -f .env ]]; then
    echo "WARNING: .env was not found. Create it on the server before serving the app." >&2
fi

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
    "${SSH_PHP_BIN}" artisan migrate --force
else
    echo "Skipping migrations. Set RUN_MIGRATIONS=true to run them during deploy."
fi

"${SSH_PHP_BIN}" artisan optimize
REMOTE_SCRIPT
