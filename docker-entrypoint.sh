#!/usr/bin/env sh
set -e

if [ "$(printf %c "$1")" = '-' ]; then
  set -- php /app/src/execute.php "$@"
elif [ "$1" = "/app/src/execute.php" ]; then
  set -- php "$@"
elif [ "$1" = "execute" ]; then
  shift
  set -- php /app/src/execute.php "$@"
fi

exec "$@"
