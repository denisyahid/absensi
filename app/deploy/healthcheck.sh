#!/usr/bin/env bash
set -euo pipefail
URL="${1:-http://127.0.0.1/backend.php?route=health}"
response="$(curl --fail --silent --show-error --max-time 10 "${URL}")"
echo "${response}" | grep -q '"status":"ok"' || { echo "Health check gagal: ${response}" >&2; exit 1; }
echo "API sehat: ${response}"
