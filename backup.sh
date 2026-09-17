#!/usr/bin/env bash
#
# Back up everything this deployment cannot regenerate: the database and the file store.
#
# Two things are backed up and the order they are taken in is deliberate. The file store goes
# first, the database second, because the database is what *refers* to the files. Taken that way
# round, anything submitted while the backup runs leaves a file nothing points at -- harmless.
# Taken the other way round it leaves a row pointing at a file the backup does not contain, which
# is a download that 404s forever.
#
# The database is dumped rather than copied. Copying `mysql_data` out from under a running MariaDB
# copies half-written pages, and the result is an archive that looks fine until the day it is
# restored. `--single-transaction` gives a consistent snapshot of InnoDB without locking anything,
# so the deployment keeps serving while this runs.
#
# `.env` is NOT included unless you ask for it (`--with-env`): it holds the database passwords and
# `JWT_SECRET`, and a backup that quietly carries secrets is a backup nobody thinks twice about
# putting on a network share. Keep it in a password manager, or pass the flag and put the result
# somewhere that deserves it. Without it a restore onto a fresh machine cannot work -- see the
# warning this prints.
#
# Usage:  ./backup.sh [-o DIR] [--keep DAYS] [--with-env]
# Cron:   0 3 * * *  cd /var/www/upolnicek && ./backup.sh -o /var/backups/upolnicek >> /var/log/upolnicek-backup.log 2>&1

set -euo pipefail

cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")"

OUT_ROOT="${UPOLNICEK_BACKUP_DIR:-./backups}"
KEEP_DAYS="${UPOLNICEK_BACKUP_KEEP_DAYS:-14}"
WITH_ENV=false

usage() {
    sed -n '3,20p' "$0" | sed 's/^# \{0,1\}//'
    exit "${1:-0}"
}

while [ $# -gt 0 ]; do
    case "$1" in
        -o|--output) OUT_ROOT="${2:?-o needs a directory}"; shift 2 ;;
        --keep) KEEP_DAYS="${2:?--keep needs a number of days}"; shift 2 ;;
        --with-env) WITH_ENV=true; shift ;;
        -h|--help) usage 0 ;;
        *) echo "Unknown option: $1" >&2; usage 1 ;;
    esac
done

case "$KEEP_DAYS" in
    ''|*[!0-9]*) echo "--keep wants a whole number of days, got '$KEEP_DAYS'" >&2; exit 1 ;;
esac

running() { [ -n "$(docker compose ps -q "$1" 2>/dev/null)" ]; }

for service in mysql api; do
    if ! running "$service"; then
        echo "The '$service' service is not running -- start the stack before backing it up." >&2
        exit 1
    fi
done

STAMP="$(date +%Y-%m-%d_%H%M%S)"
DEST="$OUT_ROOT/upolnicek-$STAMP"

if [ -e "$DEST" ]; then
    echo "$DEST already exists; refusing to write over a backup." >&2
    exit 1
fi

mkdir -p "$DEST"
chmod 700 "$DEST"

echo "Backing up to $DEST"

# --- the file store, first (see the note at the top) --------------------------------------------
echo "  file store..."
docker compose exec -T api tar czf - -C /opt/recodex-core/storage . > "$DEST/storage.tgz"

# --- the database, second -----------------------------------------------------------------------
echo "  database..."
docker compose exec -T mysql sh -c '
    mariadb-dump --single-transaction --routines --events \
        -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"
' | gzip > "$DEST/db.sql.gz"

# --- did it actually work? ----------------------------------------------------------------------
#
# Both halves are written through a pipe, and a pipe happily produces a well-formed archive out of
# an error message. So each one is opened again and asked a question only a real backup can answer.
TABLES="$(gzip -dc "$DEST/db.sql.gz" | grep -c '^CREATE TABLE' || true)"
if [ "$TABLES" -lt 10 ]; then
    echo "The dump holds $TABLES tables, which is not a database. Leaving $DEST for inspection." >&2
    exit 1
fi

FILES="$(tar tzf "$DEST/storage.tgz" | wc -l | tr -d ' ')"
if [ "$FILES" -lt 1 ]; then
    echo "The file store archive is empty. Leaving $DEST for inspection." >&2
    exit 1
fi

# --- what this is a backup *of* -----------------------------------------------------------------
#
# A restore two years from now is done by somebody who needs to know which build these bytes came
# out of. The pins are the answer, so they travel with the data rather than being looked up in a
# repository that has moved on.
{
    echo "taken:        $(date -Iseconds)"
    echo "host:         $(hostname)"
    echo "compose repo: $(git rev-parse HEAD 2>/dev/null || echo 'not a git checkout')"
    echo "tables:       $TABLES"
    echo "files:        $FILES"
    echo "env included: $WITH_ENV"
    echo
    echo "--- repos.lock ---"
    grep -vE '^\s*#|^\s*$' repos.lock 2>/dev/null || echo "(missing)"
} > "$DEST/MANIFEST"

if [ "$WITH_ENV" = true ]; then
    cp .env "$DEST/env"
    chmod 600 "$DEST/env"
    echo "  .env included -- this backup now contains passwords and JWT_SECRET."
else
    echo "  .env NOT included. Keep a copy of it somewhere safe: without JWT_SECRET and the"
    echo "  database passwords, these files cannot be restored onto a new machine."
fi

# --- retention ----------------------------------------------------------------------------------
#
# Matched on the name this script writes, so a directory somebody else put here is never a
# candidate however old it is.
if [ "$KEEP_DAYS" -gt 0 ]; then
    OLD="$(find "$OUT_ROOT" -maxdepth 1 -type d -name 'upolnicek-*' -mtime "+$KEEP_DAYS" -print)"
    if [ -n "$OLD" ]; then
        echo "  removing backups older than $KEEP_DAYS days:"
        echo "$OLD" | sed 's/^/    /'
        echo "$OLD" | while IFS= read -r dir; do rm -rf "$dir"; done
    fi
fi

echo "Done. $(du -sh "$DEST" | cut -f1) in $DEST"
echo "Restore it with:  ./restore.sh $DEST"
