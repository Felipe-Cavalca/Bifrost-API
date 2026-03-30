#!/usr/bin/env sh

set -eu

URL="${SMOKE_URL:-http://nginx/}"
TIMEOUT="${SMOKE_TIMEOUT:-30}"

end=$(( $(date +%s) + TIMEOUT ))

while [ "$(date +%s)" -lt "$end" ]; do
  code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "$URL" || true)
  if [ "$code" = "200" ]; then
    echo "API healthy at $URL (HTTP 200)"
    exit 0
  fi
  echo "Waiting for API ($URL), got $code..."
  sleep 2
done

echo "API did not become healthy within ${TIMEOUT}s at $URL"
exit 1
