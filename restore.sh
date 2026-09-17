#!/usr/bin/env bash
#
# Put a backup back. This DESTROYS the deployment's current database and file store.
#
# It restores in the mirror image of how `backup.sh` took the backup -- database first, files
# second -- and for the same reason: whichever is written last is the one a half-finished restore
# leaves incomplete, and a file with no row is harmless where a row with no file is not.
#
# Neither volume is removed. The database is dropped and recreated through SQL and the file store
# is emptied from inside a container, which does the same job without this script having to guess
# the compose project's name for `docker volume rm` -- a guess that is wrong on any deployment
# whose directory is not called `recodex` -- deploy this into `/var/www/upolnicek` and the volumes
# are `upolnicek_mysql_data` and `upolnicek_api_storage` instead.
#
# `.env` is left alone. Restoring in place uses the one already here, which is the right one. When
# restoring onto a NEW machine, put `.env` in place yourself before running this: `JWT_SECRET` and
# the database passwords are in it, and a backup only carries them if it was taken with
# `--with-env`.
#
# Usage:  ./restore.sh PATH_TO_BACKUP [--yes]

set -euo pipefail

cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")"

BACKUP=""
ASSUME_YES=false

usage() {
    sed -n '3,19p' "$0" | sed 's/^# \{0,1\}//'
    exit "${1:-0}"
}

while [ $# -gt 0 ]; do
    case "$1" in
        --yes|-y) ASSUME_YES=true; shift ;;
        -h|--help) usage 0 ;;
        -*) echo "Unknown option: $1" >&2; usage 1 ;;
        *) BACKUP="$1"; shift ;;
    esac
done

[ -n "$BACKUP" ] || usage 1

# Resolved through a second variable: the assignment lands before `||` runs, so reporting the
# failure from `$BACKUP` itself would print the empty string it had just been set to.
if ! RESOLVED="$(cd "$BACKUP" 2>/dev/null && pwd)"; then
    echo "No such directory: $BACKUP" >&2
    exit 1
fi
BACKUP="$RESOLVED"

# --- is this actually a backup? -----------------------------------------------------------------
#
# Checked before anything is stopped, let alone destroyed: discovering the archive is unreadable
# after the database has been dropped is the one failure this script must not have.
for required in db.sql.gz storage.tgz; do
    [ -f "$BACKUP/$required" ] || { echo "$BACKUP holds no $required." >&2; exit 1; }
done

TABLES="$(gzip -dc "$BACKUP/db.sql.gz" | grep -c '^CREATE TABLE' || true)"
[ "$TABLES" -ge 10 ] || { echo "$BACKUP/db.sql.gz holds $TABLES tables; that is not a database." >&2; exit 1; }
tar tzf "$BACKUP/storage.tgz" > /dev/null || { echo "$BACKUP/storage.tgz is not readable." >&2; exit 1; }

echo "About to restore from $BACKUP"
[ -f "$BACKUP/MANIFEST" ] && sed 's/^/  /' "$BACKUP/MANIFEST"
echo
echo "This ERASES the current database and every uploaded file on this deployment:"
echo "  every account, group, exercise, assignment and submitted solution is replaced"
echo "  by whatever the backup holds. There is no undo."
echo

if [ "$ASSUME_YES" != true ]; then
    printf 'Type ANO to continue: '
    read -r answer
    [ "$answer" = "ANO" ] || { echo "Nothing was changed."; exit 1; }
fi

# --- take everything but the database offline ---------------------------------------------------
#
# Nothing may be writing while this runs. mysql stays up because it is what the dump is fed to.
echo "Stopping the application..."
docker compose stop api api-worker broker monitor proxy web-app web-next worker

echo "Waiting for the database..."
docker compose up -d mysql
until docker compose exec -T mysql sh -c 'mariadb-admin ping -u root -p"$MYSQL_ROOT_PASSWORD" --silent' >/dev/null 2>&1; do
    sleep 2
done

# --- the database, first ------------------------------------------------------------------------
#
# Dropped and recreated rather than fed over the top: the dump replaces every table it contains,
# and a table that exists here and not in the backup would otherwise survive a restore and be a
# ghost nobody can account for.
echo "Restoring the database ($TABLES tables)..."
# No CHARACTER SET clause, deliberately: the server default is what the mariadb image's own
# `MYSQL_DATABASE` handling used when it first created this database (utf8mb4 on the pinned
# image), so saying nothing reproduces it exactly. Naming a collation here instead would quietly
# change it -- the restored tables would still carry their own, but every table a later migration
# adds would inherit the one this script had opinions about.
#
# The GRANT is repeated because dropping a database takes its database-level privileges with it on
# some MariaDB versions and not others; re-granting is harmless where it was not needed.
docker compose exec -T mysql sh -c '
    mariadb -u root -p"$MYSQL_ROOT_PASSWORD" -e "
        DROP DATABASE IF EXISTS \`$MYSQL_DATABASE\`;
        CREATE DATABASE \`$MYSQL_DATABASE\`;
        GRANT ALL ON \`$MYSQL_DATABASE\`.* TO \"$MYSQL_USER\"@\"%\";"
'
gzip -dc "$BACKUP/db.sql.gz" | docker compose exec -T mysql sh -c '
    mariadb -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"
'

# --- the file store, second ---------------------------------------------------------------------
#
# Through `run --rm` with the entrypoint replaced: `api` is stopped, and its real entrypoint would
# wait for the database and start migrating before the files were in place.
echo "Restoring the file store..."
docker compose run --rm --no-deps -T \
    --volume "$BACKUP:/backup:ro" \
    --entrypoint sh api -c '
        set -e
        cd /opt/recodex-core/storage
        find . -mindepth 1 -delete
        tar xzf /backup/storage.tgz -C .
        chown -R www-data:www-data .
    '

echo "Starting the application..."
docker compose up -d

echo
echo "Restored from $BACKUP."
echo "Check that it worked before you trust it: sign in, open a group, open a solution and"
echo "download its files -- a database that restored without its file store looks fine until"
echo "somebody clicks a download."
