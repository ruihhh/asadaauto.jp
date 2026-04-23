#!/usr/bin/env bash

set -euo pipefail
trap 'status=$?; echo "prepare-xserver-release.sh failed at line ${LINENO}: ${BASH_COMMAND}" >&2; exit "${status}"' ERR

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${1:-${ROOT_DIR}/.deploy}"
LARAVEL_DIR="${BUILD_DIR%/}/laravel_app"
PUBLIC_DIR="${BUILD_DIR%/}/public_html"
XSERVER_PUBLIC_DIR="${ROOT_DIR}/deploy/xserver/public_html"
XSERVER_INDEX_TEMPLATE="${XSERVER_PUBLIC_DIR}/index.php"
XSERVER_HTACCESS_TEMPLATE="${XSERVER_PUBLIC_DIR}/.htaccess"
LARAVEL_BASE_PATH="${DEPLOY_LARAVEL_BASE_PATH:-}"
NORMALIZED_LARAVEL_BASE_PATH="${LARAVEL_BASE_PATH%/}"
placeholder='/home/your_account/laravel_app'

if [[ -z "${LARAVEL_BASE_PATH}" ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH is required." >&2
    exit 1
fi

if [[ "${LARAVEL_BASE_PATH}" != /* ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH must be an absolute path, for example /home/<account>/laravel_app." >&2
    exit 1
fi

if [[ -z "${NORMALIZED_LARAVEL_BASE_PATH}" ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH must point to the Laravel app directory and cannot be '/'." >&2
    exit 1
fi

for template_path in "${XSERVER_INDEX_TEMPLATE}" "${XSERVER_HTACCESS_TEMPLATE}"; do
    if [[ ! -f "${template_path}" ]]; then
        echo "Required Xserver public template is missing: ${template_path}" >&2
        exit 1
    fi
done

template_match_count="$(grep -oF "${placeholder}" "${XSERVER_INDEX_TEMPLATE}" | wc -l | tr -d ' ' || true)"

if [[ "${template_match_count}" != "1" ]]; then
    echo "Xserver index template must contain exactly one Laravel base path placeholder." >&2
    echo "Expected placeholder: ${placeholder}" >&2
    echo "Template path: ${XSERVER_INDEX_TEMPLATE}" >&2
    echo "Template laravelBasePath line:" >&2
    grep -n '\$laravelBasePath' "${XSERVER_INDEX_TEMPLATE}" >&2 || true
    exit 1
fi

rm -rf "${BUILD_DIR}"
mkdir -p "${LARAVEL_DIR}" "${PUBLIC_DIR}"

rsync -a --delete \
    --exclude '.git/' \
    --exclude '.github/' \
    --exclude '.claude/' \
    --exclude '.deploy/' \
    --exclude '.deploy-debug/' \
    --exclude '.deploy*/' \
    --exclude 'node_modules/' \
    --exclude 'vendor/' \
    --exclude 'tests/' \
    --exclude 'docs/' \
    --exclude 'docker/' \
    --exclude 'deploy/xserver/public_html/' \
    --exclude '.env' \
    --exclude '.env.backup' \
    --exclude '.env.production' \
    --exclude 'public/storage' \
    --exclude 'storage/' \
    "${ROOT_DIR}/" "${LARAVEL_DIR}/"

rsync -a --delete \
    --exclude 'storage' \
    "${ROOT_DIR}/public/" "${PUBLIC_DIR}/"

cp -f "${XSERVER_INDEX_TEMPLATE}" "${PUBLIC_DIR}/index.php"
cp -f "${XSERVER_HTACCESS_TEMPLATE}" "${PUBLIC_DIR}/.htaccess"

match_count="$(grep -oF "${placeholder}" "${PUBLIC_DIR}/index.php" | wc -l | tr -d ' ' || true)"

if [[ "${match_count}" != "1" ]]; then
    echo "Failed to locate the Laravel base path placeholder in ${PUBLIC_DIR}/index.php." >&2
    echo "Expected exactly one occurrence of: ${placeholder}" >&2
    echo "Current laravelBasePath line:" >&2
    grep -n '\$laravelBasePath' "${PUBLIC_DIR}/index.php" >&2 || true
    echo "Current index.php first 30 lines:" >&2
    sed -n '1,30p' "${PUBLIC_DIR}/index.php" >&2 || true
    exit 1
fi

export DEPLOY_LARAVEL_BASE_PATH="${NORMALIZED_LARAVEL_BASE_PATH}"
perl -0pi -e 's/\Q\/home\/your_account\/laravel_app\E/$ENV{DEPLOY_LARAVEL_BASE_PATH}/g' "${PUBLIC_DIR}/index.php"

echo "Prepared release bundle:"
echo "  Laravel app: ${LARAVEL_DIR}"
echo "  public_html: ${PUBLIC_DIR}"
