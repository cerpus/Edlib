#!/bin/bash
# wait-for-multiple.sh

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

set -e

for var in "$@"
do
  bash -c "$DIR/wait-for-it.sh $var -t 0"
done


>&2 echo "Everything is up"