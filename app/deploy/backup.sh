#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ROOT}/.env"
BACKUP_DIR="${BACKUP_DIR:-${ROOT}/backups}"
STAMP="$(date +%Y%m%d_%H%M%S)"
WORK="${BACKUP_DIR}/absensi_${STAMP}"

[[ -f "${ENV_FILE}" ]] || { echo "File .env tidak ditemukan" >&2; exit 1; }
mkdir -p "${WORK}"

read_env() {
  local key="$1"
  sed -n "s/^${key}=//p" "${ENV_FILE}" | tail -n1 | sed -e 's/^"//' -e 's/"$//'
}

DB_CONNECTION="$(read_env DB_CONNECTION)"
if [[ "${DB_CONNECTION:-sqlite}" == "sqlite" ]]; then
  DB_DATABASE="$(read_env DB_DATABASE)"
  [[ -n "${DB_DATABASE}" ]] || DB_DATABASE="${ROOT}/database/database.sqlite"
  [[ "${DB_DATABASE}" = /* ]] || DB_DATABASE="${ROOT}/${DB_DATABASE}"
  [[ -f "${DB_DATABASE}" ]] || { echo "Database SQLite tidak ditemukan: ${DB_DATABASE}" >&2; exit 1; }
  cp "${DB_DATABASE}" "${WORK}/database.sqlite"
else
  DB_HOST="$(read_env DB_HOST)"; DB_PORT="$(read_env DB_PORT)"; DB_DATABASE="$(read_env DB_DATABASE)"; DB_USERNAME="$(read_env DB_USERNAME)"; DB_PASSWORD="$(read_env DB_PASSWORD)"
  MYSQL_PWD="${DB_PASSWORD}" mysqldump --single-transaction --quick --host="${DB_HOST:-127.0.0.1}" --port="${DB_PORT:-3306}" --user="${DB_USERNAME}" "${DB_DATABASE}" > "${WORK}/database.sql"
fi

[[ -d "${ROOT}/storage/app/public" ]] && tar -czf "${WORK}/storage-public.tar.gz" -C "${ROOT}/storage/app" public
cp "${ENV_FILE}" "${WORK}/env.snapshot"
tar -czf "${BACKUP_DIR}/absensi_${STAMP}.tar.gz" -C "${BACKUP_DIR}" "absensi_${STAMP}"
rm -rf "${WORK}"
echo "Backup selesai: ${BACKUP_DIR}/absensi_${STAMP}.tar.gz"
