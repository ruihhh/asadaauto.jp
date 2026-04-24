#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${1:-${RUNNER_TEMP:-/tmp}/asadaauto-deploy-debug}"

section() {
    local title="$1"

    if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
        echo "::group::${title}"
    else
        echo
        echo "== ${title} =="
    fi
}

end_section() {
    if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
        echo "::endgroup::"
    fi
}

print_value() {
    local name="$1"
    local value="${!name:-}"

    if [[ -z "${value}" ]]; then
        echo "${name}: <not set>"
    else
        echo "${name}: ${value}"
    fi
}

print_secret_status() {
    local name="$1"
    local value="${!name:-}"

    if [[ -z "${value}" ]]; then
        echo "${name}: <not set>"
    else
        echo "${name}: <set, hidden>"
    fi
}

warn() {
    if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
        echo "::warning::$1"
    else
        echo "WARNING: $1"
    fi
}

failures=0

section "Deployment variables"
print_secret_status FTP_SERVER
print_secret_status FTP_USERNAME
print_secret_status FTP_PASSWORD
print_value FTP_PORT
print_value FTP_PROTOCOL
print_value DEPLOY_LARAVEL_BASE_PATH
print_value FTP_LARAVEL_APP_DIR
print_value FTP_PUBLIC_HTML_DIR
print_value SSH_PORT
print_value SSH_PHP_BIN
print_value SSH_COMPOSER_BIN
print_value SSH_CONNECT_RETRIES
print_value SSH_CONNECT_RETRY_DELAY
print_value SSH_COMPOSER_MEMORY_LIMIT
print_value RUN_MIGRATIONS
end_section

section "Variable checks"
if [[ -z "${DEPLOY_LARAVEL_BASE_PATH:-}" ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH is required."
    failures=$((failures + 1))
elif [[ "${DEPLOY_LARAVEL_BASE_PATH}" != /* ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH must be an absolute server path."
    failures=$((failures + 1))
elif [[ "${DEPLOY_LARAVEL_BASE_PATH%/}" == "" ]]; then
    echo "DEPLOY_LARAVEL_BASE_PATH cannot be '/'."
    failures=$((failures + 1))
fi

if [[ "${DEPLOY_LARAVEL_BASE_PATH:-}" == *"/public_html/"* ]]; then
    warn "DEPLOY_LARAVEL_BASE_PATH contains /public_html/. Laravel本体は通常 public_html の外に置きます。"
fi

for dir_var in FTP_LARAVEL_APP_DIR FTP_PUBLIC_HTML_DIR; do
    value="${!dir_var:-}"

    if [[ -z "${value}" ]]; then
        echo "${dir_var} is required."
        failures=$((failures + 1))
    elif [[ "${value}" != */ ]]; then
        echo "${dir_var} must end with a trailing slash (/)."
        failures=$((failures + 1))
    fi
done

if [[ "${FTP_PUBLIC_HTML_DIR:-}" == "public/" ]]; then
    warn "FTP_PUBLIC_HTML_DIR is public/. Xserverでは public_html/ または public_html/b-2026.asadaauto.jp/ の可能性が高いです。"
fi

normalized_public_html_dir="${FTP_PUBLIC_HTML_DIR:-}"
normalized_public_html_dir="${normalized_public_html_dir%/}"

if [[ "${normalized_public_html_dir}" == "public_html" || "${normalized_public_html_dir}" == */public_html ]]; then
    echo "FTP_PUBLIC_HTML_DIR points to the legacy FuelPHP document root. Use public_html/b-2026.asadaauto.jp/ for this Laravel subdomain."
    failures=$((failures + 1))
fi
end_section

section "Required commands"
for command_name in rsync grep perl find sed wc; do
    if command -v "${command_name}" >/dev/null 2>&1; then
        echo "${command_name}: $(command -v "${command_name}")"
    else
        echo "${command_name}: missing"
        failures=$((failures + 1))
    fi
done
end_section

section "Repository files"
for file_path in \
    "deploy/xserver/public_html/index.php" \
    "deploy/xserver/public_html/.htaccess" \
    "public/.htaccess" \
    "composer.json" \
    "package.json"
do
    if [[ -f "${ROOT_DIR}/${file_path}" ]]; then
        echo "${file_path}: found"
    else
        echo "${file_path}: missing"
        failures=$((failures + 1))
    fi
done

if [[ -f "${ROOT_DIR}/deploy/xserver/public_html/index.php" ]]; then
    echo "Template index.php first 20 lines:"
    sed -n '1,20p' "${ROOT_DIR}/deploy/xserver/public_html/index.php" || true

    echo "Template laravelBasePath line:"
    grep -n '\$laravelBasePath' "${ROOT_DIR}/deploy/xserver/public_html/index.php" || true

    placeholder_count="$(grep -oF '/home/your_account/laravel_app' "${ROOT_DIR}/deploy/xserver/public_html/index.php" | wc -l | tr -d ' ' || true)"
    echo "Template placeholder count: ${placeholder_count}"
fi
end_section

section "Build artifacts"
for file_path in "public/build/manifest.json"; do
    if [[ -e "${ROOT_DIR}/${file_path}" ]]; then
        echo "${file_path}: found"
    else
        echo "${file_path}: not found yet"
    fi
done

if [[ -e "${ROOT_DIR}/vendor/autoload.php" ]]; then
    echo "vendor/autoload.php: found locally, but vendor/ is intentionally excluded from FTP upload"
else
    echo "vendor/autoload.php: not found locally; this is OK because composer install runs on the server"
fi
end_section

section "Prepare release dry run"
if [[ "${failures}" -gt 0 ]]; then
    echo "Skipping prepare dry run because required checks failed."
else
    echo "Running scripts/prepare-xserver-release.sh with debug output dir: ${BUILD_DIR}"

    if "${ROOT_DIR}/scripts/prepare-xserver-release.sh" "${BUILD_DIR}"; then
        echo "Prepare dry run: success"
    else
        status=$?
        echo "Prepare dry run: failed with exit code ${status}"
        failures=$((failures + 1))
    fi
fi
end_section

if [[ -d "${BUILD_DIR}" ]]; then
    section "Generated bundle checks"
    for file_path in \
        "laravel_app/artisan" \
        "public_html/index.php" \
        "public_html/.htaccess"
    do
        if [[ -e "${BUILD_DIR}/${file_path}" ]]; then
            echo "${file_path}: found"
        else
            echo "${file_path}: missing"
        fi
    done

    if [[ -d "${BUILD_DIR}/laravel_app/vendor" ]]; then
        echo "laravel_app/vendor: unexpectedly found"
    else
        echo "laravel_app/vendor: intentionally excluded"
    fi

    if [[ -d "${BUILD_DIR}/laravel_app/storage" ]]; then
        echo "laravel_app/storage: unexpectedly found"
    else
        echo "laravel_app/storage: intentionally excluded"
    fi

    if [[ -f "${BUILD_DIR}/public_html/index.php" ]]; then
        echo "Generated public_html/index.php first 20 lines:"
        sed -n '1,20p' "${BUILD_DIR}/public_html/index.php" || true

        echo "Generated public_html/index.php laravelBasePath line:"
        grep -n '\$laravelBasePath' "${BUILD_DIR}/public_html/index.php" || true
    fi

    echo "Generated public_html top-level entries:"
    find "${BUILD_DIR}/public_html" -maxdepth 1 -mindepth 1 -print | sed "s#${BUILD_DIR}/public_html/##" | sort
    end_section
fi

if [[ "${failures}" -gt 0 ]]; then
    echo "Diagnostics finished with ${failures} problem(s)." >&2
    exit 1
fi

echo "Diagnostics finished without blocking problems."
