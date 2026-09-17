#!/usr/bin/env bash
#
# Render every email template to `data/email-preview/`, so the wording can be read before it is
# sent. The renderer runs inside the `api` container, because that is where core-api's own Latte
# engine, its filters and the deployment's real configuration live -- a preview built anywhere else
# would be guessing at the addresses, which are the half worth checking.
#
# Usage:  tools/email-preview.sh [OUTPUT_DIR]

set -euo pipefail

cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")/.."

OUT="${1:-data/email-preview}"
IN_CONTAINER="/tmp/email-preview"

TEMPLATES="/tmp/email-templates"

docker compose exec -T api rm -rf "$IN_CONTAINER" "$TEMPLATES"
docker compose exec -T api mkdir -p /opt/recodex-core/tools
docker compose cp tools/email-preview.php api:/opt/recodex-core/tools/email-preview.php

# The templates come from the working tree, not from the image the container was built with: the
# point of a preview is to read wording before it is deployed. Copied in rather than mounted,
# because a mount would mean recreating the container and taking the deployment down to read some
# text. The copy lives under /tmp and is thrown away on the next run.
docker compose cp repos/api/app/helpers/Emails "api:$TEMPLATES"

docker compose exec -T api php /opt/recodex-core/tools/email-preview.php "$IN_CONTAINER" "$TEMPLATES"

rm -rf "$OUT"
mkdir -p "$(dirname "$OUT")"
docker compose cp "api:$IN_CONTAINER" "$OUT"

echo
echo "Previews in $OUT -- open one in a browser, or read $OUT/SUBJECTS.txt for the subject lines."
