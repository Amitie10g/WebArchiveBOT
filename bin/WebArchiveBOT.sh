#!/usr/bin/env bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
MAIN=$DIR/main.php

exec /usr/bin/php $MAIN
