#!/usr/bin/env bash
# fetch-firebase-vendor.sh
# Usage:
#   cd /path/to/your/project/root
#   ./assets/tools/fetch-firebase-vendor.sh

set -euo pipefail
DEST="assets/js/vendor"
mkdir -p "$DEST"
versions=("9.24.0" "9.23.0" "9.22.0" "9.21.0")
cdnBases=( "https://www.gstatic.com/firebasejs/%s/" "https://cdn.jsdelivr.net/npm/firebase@%s/" "https://unpkg.com/firebase@%s/dist/" )
files=("firebase-app.js" "firebase-auth.js" "firebase-firestore.js")

for file in "${files[@]}"; do
  ok=0
  for v in "${versions[@]}"; do
    for baseFmt in "${cdnBases[@]}"; do
      base=$(printf "$baseFmt" "$v")
      url="$base$file"
      out="$DEST/$file"
      echo "Trying $url"
      if curl -fLsS "$url" -o "$out"; then
        head=$(head -n 1 "$out" || true)
        if echo "$head" | grep -q -e '<!DOCTYPE' -e '<html'; then
          echo "Downloaded $file looks like HTML, removing and continuing"
          rm -f "$out"
        else
          echo "Downloaded $file from $url"
          ok=1
          break 2
        fi
      else
        echo "Failed to fetch $url"
      fi
    done
  done
  if [ $ok -ne 1 ]; then
    echo "ERROR: Failed to download $file from all CDNs" >&2
  fi
done

echo "Done. Files in $DEST:"
ls -l "$DEST"
