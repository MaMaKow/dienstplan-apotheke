#!/bin/bash
set -euo pipefail
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

cleanup() {
  bash "$script_dir/bin/test-db.sh" down
}
trap cleanup EXIT

bash "$script_dir/bin/test-db.sh" up
"$script_dir/../vendor/bin/phpunit" --testsuite=integration "$@"
