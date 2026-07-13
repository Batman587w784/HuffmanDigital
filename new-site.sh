#!/usr/bin/env bash
# Scaffold a new client site folder from _template.
# Usage: ./new-site.sh "Client Business Name"

set -euo pipefail

if [ -z "${1:-}" ]; then
  echo 'Usage: ./new-site.sh "Client Business Name"' >&2
  exit 1
fi

NAME="$1"
SLUG=$(echo "$NAME" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+|-+$//g')

if [ -z "$SLUG" ]; then
  echo "Could not derive a folder name from: $NAME" >&2
  exit 1
fi

if [ -e "$SLUG" ]; then
  echo "Folder already exists: $SLUG" >&2
  exit 1
fi

cp -R _template "$SLUG"
sed -i "s/Client Business Name/${NAME//\//\\/}/g" "$SLUG/site.html"

echo "Created $SLUG/"
echo "Edit $SLUG/site.html, then commit and push to main."
