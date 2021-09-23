#!/bin/bash

cd "$(dirname "$0")"

ZSHRC_LOCATION=~/.zshrc
BASHRC_LOCATION=~/.bashrc

read -r -d '' aliases << EOM
alias dc="docker-compose"
alias dcu="dc up -d"
alias dcl="dc logs -f"
alias dcd="dc down"
alias dcr="dc restart"
EOM

replaceStringWithoutNewline=${aliases//$'\n'/\\n}

./manage-block-in-file.sh $BASHRC_LOCATION "$replaceStringWithoutNewline"

if test -f "$ZSHRC_LOCATION"; then
  echo "Found .zshrc and we are therefore adding aliases there as well."
  ./manage-block-in-file.sh $ZSHRC_LOCATION "$replaceStringWithoutNewline"
fi

source ~/.bashrc
