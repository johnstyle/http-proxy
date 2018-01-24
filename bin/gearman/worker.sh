#!/usr/bin/env bash

ROOT_PATH=$(dirname $(dirname $(dirname $(readlink -f $0))))

set -a
. "${ROOT_PATH}/.env"
set +a

php "${ROOT_PATH}/bin/worker" > "${ROOT_PATH}/var/log/worker.log" 2>&1
