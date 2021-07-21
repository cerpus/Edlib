#!/bin/bash

cd "$(dirname "$0")"

read -r -d '' aliases << EOM
alias dc="docker-compose"
alias dcu="dc up -d"
alias dcl="dc logs -f"
alias dcd="dc down"
alias dcr="dc restart"
EOM

replaceStringWithoutNewline=${aliases//$'\n'/\\n}

./manage-block-in-file.sh ~/.bashrc "$replaceStringWithoutNewline"

source ~/.bashrc
